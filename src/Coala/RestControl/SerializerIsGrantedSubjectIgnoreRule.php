<?php declare(strict_types=1);

/**
 * PHPStan Rules
 *
 * @package   PHPStan Rules
 * @author    IWF Web Solutions <web-solutions@iwf.ch>
 * @copyright Copyright (c) 2025-2026 IWF Web Solutions <web-solutions@iwf.ch>
 * @license   https://github.com/iwf-web/phpstan-rules/blob/main/LICENSE.txt MIT License
 * @link      https://github.com/iwf-web/phpstan-rules
 */

namespace IWFWeb\PhpstanRules\Coala\RestControl;

use Coala\RestControlBundle\Service\Serializer\AppSerializer;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * A controller method guarded by #[IsGranted(..., subject: 'x')] that deserializes the request
 * body into a message object must mark every message property derived from that subject with
 * #[Ignore]. Otherwise the request body can overwrite the value the voter just authorized.
 *
 * A property is considered subject-derived when it is named `{subject}` / `{subject}Id`
 * or its native type is the subject parameter's class.
 *
 * @implements Rule<InClassMethodNode>
 */
final readonly class SerializerIsGrantedSubjectIgnoreRule implements Rule
{
    public const string IDENTIFIER = 'iwfWeb.serializerIsGrantedSubjectIgnore';

    private const string SERIALIZER_CLASS = AppSerializer::class;
    private const string IS_GRANTED_ATTRIBUTE = IsGranted::class;
    private const array IGNORE_ATTRIBUTES = [
        'Symfony\Component\Serializer\Attribute\Ignore',
        'Symfony\Component\Serializer\Annotation\Ignore',
    ];
    private const array SERIALIZER_METHODS = ['deserializeIntoExistingObject', 'deserialize'];

    /**
     * @param list<array{attribute: string, argument: string}> $entityReferenceAttributes
     *                                                                                    Validator attributes whose `argument` names the referenced entity class, e.g. EntityExists(entityClass: ...)
     */
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private array $entityReferenceAttributes = [
            ['attribute' => 'App\Validator\Doctrine\EntityExists', 'argument' => 'entityClass'],
        ],
    ) {}

    #[\Override]
    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    /**
     * @param InClassMethodNode $node
     *
     * @return list<IdentifierRuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->reflectionProvider->hasClass(self::SERIALIZER_CLASS)) {
            return [];
        }

        $method = $node->getOriginalNode();
        if ($method->stmts === null) {
            return [];
        }

        $subjects = $this->collectSubjects($method);
        if ($subjects === []) {
            return [];
        }

        $subjectClasses = $this->collectSubjectClasses($scope, $subjects);

        $errors = [];

        foreach ($this->findSerializerCalls($method, $scope) as $call) {
            $message = $this->resolveMessage($call, $method, $scope, $subjects);
            if ($message === null) {
                continue;
            }

            [$messageClass, $wired] = $message;

            foreach ($this->findUnprotectedProperties($messageClass, $subjects, $subjectClasses, $wired) as [$property, $subject]) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Property %s::$%s is derived from #[IsGranted] subject $%s but can be overwritten by the request body.',
                    $messageClass->getDisplayName(),
                    $property,
                    $subject,
                ))
                    ->identifier(self::IDENTIFIER)
                    ->line($call->getStartLine())
                    ->tip('Add #[Ignore] to the property and set it from the authorized controller argument instead.')
                    ->build()
                ;
            }
        }

        return $errors;
    }

    /**
     * @return list<string> parameter names used as #[IsGranted] subject
     */
    private function collectSubjects(ClassMethod $method): array
    {
        $subjects = [];

        foreach ($method->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() !== self::IS_GRANTED_ATTRIBUTE) {
                    continue;
                }

                foreach ($attr->args as $index => $arg) {
                    $isSubject = $arg->name instanceof Identifier ? $arg->name->toString() === 'subject' : $index === 1;
                    if (!$isSubject) {
                        continue;
                    }

                    array_push($subjects, ...$this->extractSubjectNames($arg->value));
                }
            }
        }

        return array_values(array_unique($subjects));
    }

    /**
     * @return list<string>
     */
    private function extractSubjectNames(Expr $value): array
    {
        if ($value instanceof String_) {
            return [$value->value];
        }

        if (!$value instanceof Array_) {
            return [];
        }

        $names = [];
        foreach ($value->items as $item) {
            if ($item->value instanceof String_) {
                $names[] = $item->value->value;
            }
        }

        return $names;
    }

    /**
     * @param list<string> $subjects
     *
     * @return array<string, list<string>> subject name => class names of its parameter type
     */
    private function collectSubjectClasses(Scope $scope, array $subjects): array
    {
        $function = $scope->getFunction();
        if ($function === null) {
            return [];
        }

        $classes = [];
        foreach ($function->getParameters() as $parameter) {
            if (\in_array($parameter->getName(), $subjects, true)) {
                $classes[$parameter->getName()] = TypeCombinator::removeNull($parameter->getType())->getObjectClassNames();
            }
        }

        return $classes;
    }

    /**
     * @return list<MethodCall>
     */
    private function findSerializerCalls(ClassMethod $method, Scope $scope): array
    {
        $serializerType = new ObjectType(self::SERIALIZER_CLASS);
        $calls = [];

        /** @var MethodCall $call */
        foreach ((new NodeFinder())->findInstanceOf($method->stmts ?? [], MethodCall::class) as $call) {
            if (!$call->name instanceof Identifier || !\in_array($call->name->toString(), self::SERIALIZER_METHODS, true)) {
                continue;
            }

            if (!$serializerType->isSuperTypeOf($scope->getType($call->var))->yes()) {
                continue;
            }

            $calls[] = $call;
        }

        return $calls;
    }

    /**
     * Resolves the message class the body is deserialized into, plus the properties that the
     * controller wires from an #[IsGranted] subject (constructor arguments or property assignments).
     *
     * @param list<string> $subjects
     *
     * @return null|array{ClassReflection, array<string, string>} [class, property => subject]
     */
    private function resolveMessage(MethodCall $call, ClassMethod $method, Scope $scope, array $subjects): ?array
    {
        $args = $call->getArgs();
        if (!isset($args[1])) {
            return null;
        }

        $expr = $args[1]->value;
        $new = null;
        $variables = [];

        if ($expr instanceof New_) {
            $new = $expr;
        } elseif ($expr instanceof ClassConstFetch && $expr->class instanceof Name && $expr->name instanceof Identifier && $expr->name->toString() === 'class') {
            $fqcn = $scope->resolveName($expr->class);
        } elseif ($expr instanceof Variable && \is_string($expr->name)) {
            $variables[] = $expr->name;
            // ponytail: last `$var = new X(...)` in the method body; no flow analysis
            foreach ($this->findAssignments($method) as $assign) {
                if ($assign->var instanceof Variable && $assign->var->name === $expr->name && $assign->expr instanceof New_) {
                    $new = $assign->expr;
                }
            }
        }

        if ($new !== null) {
            if (!$new->class instanceof Name) {
                return null;
            }
            $fqcn = $scope->resolveName($new->class);
        }

        if (!isset($fqcn) || !$this->reflectionProvider->hasClass($fqcn)) {
            return null;
        }

        $messageClass = $this->reflectionProvider->getClass($fqcn);
        $wired = $new !== null ? $this->wiredByConstructor($messageClass, $new, $subjects) : [];

        foreach ($this->findAssignments($method) as $assign) {
            if ($assign->var instanceof Variable && \is_string($assign->var->name) && $assign->expr === $call) {
                $variables[] = $assign->var->name;
            }
        }

        return [$messageClass, [...$wired, ...$this->wiredByAssignment($method, $variables, $subjects)]];
    }

    /**
     * @return list<ExtendedParameterReflection>
     */
    private function parameters(ExtendedMethodReflection $method): array
    {
        $variant = $method->getVariants()[0] ?? null;

        return $variant === null ? [] : $variant->getParameters();
    }

    /**
     * @return list<Assign>
     */
    private function findAssignments(ClassMethod $method): array
    {
        $assignments = [];
        foreach ((new NodeFinder())->findInstanceOf($method->stmts ?? [], Assign::class) as $assignment) {
            $assignments[] = $assignment;
        }

        return $assignments;
    }

    /**
     * Maps constructor arguments that reference a subject variable to their promoted property.
     *
     * @param list<string> $subjects
     *
     * @return array<string, string> property => subject
     */
    private function wiredByConstructor(ClassReflection $messageClass, New_ $new, array $subjects): array
    {
        $constructor = $messageClass->hasConstructor() ? $messageClass->getConstructor() : null;
        if ($constructor === null) {
            return [];
        }

        $parameters = $this->parameters($constructor);
        $wired = [];

        foreach ($new->getArgs() as $index => $arg) {
            $subject = $this->referencedSubject($arg->value, $subjects);
            if ($subject === null) {
                continue;
            }

            $parameterName = $arg->name instanceof Identifier
                ? $arg->name->toString()
                : ($parameters[$index] ?? null)?->getName();

            if ($parameterName !== null && $messageClass->hasNativeProperty($parameterName)) {
                $wired[$parameterName] = $subject;
            }
        }

        return $wired;
    }

    /**
     * Finds `$var->property = <expr referencing a subject>` anywhere in the method.
     *
     * @param list<string> $variables
     * @param list<string> $subjects
     *
     * @return array<string, string> property => subject
     */
    private function wiredByAssignment(ClassMethod $method, array $variables, array $subjects): array
    {
        $wired = [];

        foreach ($this->findAssignments($method) as $assign) {
            $target = $assign->var;
            if (!$target instanceof PropertyFetch || !$target->var instanceof Variable || !$target->name instanceof Identifier) {
                continue;
            }

            if (!\in_array($target->var->name, $variables, true)) {
                continue;
            }

            $subject = $this->referencedSubject($assign->expr, $subjects);
            if ($subject !== null) {
                $wired[$target->name->toString()] = $subject;
            }
        }

        return $wired;
    }

    /**
     * @param list<string> $subjects
     */
    private function referencedSubject(Expr $expr, array $subjects): ?string
    {
        $variable = (new NodeFinder())->findFirst(
            $expr,
            static fn (Node $node): bool => $node instanceof Variable && \is_string($node->name) && \in_array($node->name, $subjects, true),
        );

        return $variable instanceof Variable && \is_string($variable->name) ? $variable->name : null;
    }

    /**
     * @param list<string>                $subjects
     * @param array<string, list<string>> $subjectClasses
     * @param array<string, string>       $wired          property => subject
     *
     * @return list<array{string, string}> [property name, subject name]
     */
    private function findUnprotectedProperties(ClassReflection $messageClass, array $subjects, array $subjectClasses, array $wired): array
    {
        $unprotected = [];

        foreach ($messageClass->getNativeReflection()->getProperties() as $nativeProperty) {
            if ($nativeProperty->isStatic()) {
                continue;
            }

            $name = $nativeProperty->getName();
            if (!$nativeProperty->isPublic() && !$messageClass->hasMethod('set'.ucfirst($name))) {
                continue;
            }

            $subject = $wired[$name] ?? $this->matchSubject($messageClass, $name, $subjects, $subjectClasses);
            if ($subject === null) {
                continue;
            }

            foreach ($nativeProperty->getAttributes() as $attribute) {
                if (\in_array($attribute->getName(), self::IGNORE_ATTRIBUTES, true)) {
                    continue 2;
                }
            }

            $unprotected[] = [$name, $subject];
        }

        return $unprotected;
    }

    /**
     * @param list<string>                $subjects
     * @param array<string, list<string>> $subjectClasses
     */
    private function matchSubject(ClassReflection $messageClass, string $property, array $subjects, array $subjectClasses): ?string
    {
        foreach ($subjects as $subject) {
            if ($property === $subject || $property === $subject.'Id') {
                return $subject;
            }
        }

        if (!$messageClass->hasNativeProperty($property)) {
            return null;
        }

        $nativeProperty = $messageClass->getNativeProperty($property);
        $propertyClasses = [
            ...TypeCombinator::removeNull($nativeProperty->getNativeType())->getObjectClassNames(),
            ...$this->referencedEntityClasses($nativeProperty->getNativeReflection()),
        ];

        foreach ($subjectClasses as $subject => $classes) {
            if (array_intersect($propertyClasses, $classes) !== []) {
                return $subject;
            }
        }

        return null;
    }

    /**
     * Entity classes named by configured validator attributes, e.g. #[EntityExists(entityClass: Employer::class)].
     *
     * @return list<string>
     */
    private function referencedEntityClasses(\ReflectionProperty $property): array
    {
        $classes = [];

        foreach ($this->entityReferenceAttributes as ['attribute' => $attributeClass, 'argument' => $argument]) {
            foreach ($property->getAttributes($attributeClass) as $attribute) {
                $arguments = $attribute->getArguments();
                $value = $arguments[$argument] ?? $arguments[$this->argumentPosition($attributeClass, $argument)] ?? null;

                if (\is_string($value)) {
                    $classes[] = ltrim($value, '\\');
                }
            }
        }

        return $classes;
    }

    private function argumentPosition(string $attributeClass, string $argument): int
    {
        if (!$this->reflectionProvider->hasClass($attributeClass)) {
            return -1;
        }

        $class = $this->reflectionProvider->getClass($attributeClass);
        if (!$class->hasConstructor()) {
            return -1;
        }

        foreach ($this->parameters($class->getConstructor()) as $position => $parameter) {
            if ($parameter->getName() === $argument) {
                return $position;
            }
        }

        return -1;
    }
}
