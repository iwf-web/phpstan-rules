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

namespace IWF\PhpstanRules\Controller;

use IWF\PhpstanRules\Concern\AttributeFinderTrait;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Ensures controller actions that return $this->handle() use "object" as return type.
 *
 * Since HandleTrait::handle() returns mixed, declaring a specific return type
 * (e.g. RecordsResponse) causes a fatal TypeError when the handler returns
 * an unexpected type like ErrorResponse.
 *
 * @implements Rule<Class_>
 */
final class ControllerHandleReturnTypeRule implements Rule
{
    use AttributeFinderTrait;

    public const string IDENTIFIER = 'iwf.controllerHandleReturnType';
    private const string ROUTE_ATTRIBUTE = 'Symfony\Component\Routing\Attribute\Route';
    private const string HANDLE_TRAIT = 'Symfony\Component\Messenger\HandleTrait';

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly string $controllerNamespace = 'App\Controller',
    ) {}

    #[\Override]
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     *
     * @return list<IdentifierRuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name === null) {
            return [];
        }

        $namespace = $scope->getNamespace();
        if ($namespace === null || !str_starts_with($namespace, $this->controllerNamespace)) {
            return [];
        }

        $className = $namespace.'\\'.$node->name->toString();

        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$this->usesHandleTrait($classReflection)) {
            return [];
        }

        $errors = [];

        foreach ($node->getMethods() as $method) {
            if (!$this->methodHasAttribute($method, self::ROUTE_ATTRIBUTE)) {
                continue;
            }

            $returnLine = $this->findHandleReturnLine($method);
            if ($returnLine === null) {
                continue;
            }

            $returnType = $method->getReturnType();

            if ($returnType === null || $this->isAcceptableReturnType($returnType)) {
                continue;
            }

            $methodName = $method->name->toString();
            $message = \sprintf(
                'Controller %s::%s() returns $this->handle() result but has return type "%s" which may cause a TypeError.',
                $node->name->toString(),
                $methodName,
                $this->returnTypeToString($returnType),
            );

            $errors[] = RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->line($returnLine)
                ->tip(\sprintf('Change the return type of %s() to "object" to safely handle all response types from the message bus.', $methodName))
                ->build()
            ;
        }

        return $errors;
    }

    private function usesHandleTrait(ClassReflection $classReflection): bool
    {
        return $this->classUsesTraitRecursive($classReflection, self::HANDLE_TRAIT);
    }

    private function classUsesTraitRecursive(ClassReflection $classReflection, string $traitName): bool
    {
        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->getName() === $traitName) {
                return true;
            }

            if ($this->classUsesTraitRecursive($trait, $traitName)) {
                return true;
            }
        }

        $parent = $classReflection->getParentClass();
        if ($parent !== null) {
            return $this->classUsesTraitRecursive($parent, $traitName);
        }

        return false;
    }

    /**
     * Finds the line of the return statement that returns $this->handle()'s result:
     * - return $this->handle(...)
     * - return $this->foo($this->handle(...), ...)
     * - $x = $this->handle(...); return $x;
     *
     * Returns the line number of the offending return statement, or null if none found.
     */
    private function findHandleReturnLine(Node\Stmt\ClassMethod $method): ?int
    {
        $stmts = $method->stmts ?? [];
        if ($stmts === []) {
            return null;
        }

        $nodeFinder = new NodeFinder();

        // Collect variable names assigned from $this->handle()
        $handleVars = [];

        /** @var Node\Expr\Assign[] $assigns */
        $assigns = $nodeFinder->find($stmts, static fn (Node $node): bool => $node instanceof Node\Expr\Assign && self::isThisHandleCall($node->expr),
        );

        foreach ($assigns as $assign) {
            if ($assign->var instanceof Variable && \is_string($assign->var->name)) {
                $handleVars[] = $assign->var->name;
            }
        }

        // Check if any return statement's expression contains $this->handle()
        // or returns a variable that was assigned from $this->handle()
        /** @var Node\Stmt\Return_[] $returns */
        $returns = $nodeFinder->find($stmts, static fn (Node $node): bool => $node instanceof Node\Stmt\Return_);

        foreach ($returns as $return) {
            if ($return->expr === null) {
                continue;
            }

            // $this->handle() anywhere in the returned expression tree
            // (covers direct return, nested in method args, etc.)
            $handleInExpr = $nodeFinder->findFirst($return->expr, static fn (Node $node): bool => self::isThisHandleCall($node),
            );

            if ($handleInExpr !== null) {
                return $return->getLine();
            }

            // return $var where $var was assigned from $this->handle()
            if (
                $return->expr instanceof Variable
                && \is_string($return->expr->name)
                && \in_array($return->expr->name, $handleVars, true)
            ) {
                return $return->getLine();
            }
        }

        return null;
    }

    private static function isThisHandleCall(Node $node): bool
    {
        return $node instanceof MethodCall
            && $node->var instanceof Variable
            && $node->var->name === 'this'
            && $node->name instanceof Node\Identifier
            && $node->name->toString() === 'handle';
    }

    private function isAcceptableReturnType(Node $returnType): bool
    {
        if ($returnType instanceof Node\Identifier) {
            return \in_array($returnType->toString(), ['object', 'mixed'], true);
        }

        if ($returnType instanceof Node\UnionType) {
            foreach ($returnType->types as $type) {
                if ($type instanceof Node\Identifier && $type->toString() === 'object') {
                    return true;
                }
            }

            return false;
        }

        if ($returnType instanceof Node\NullableType) {
            $inner = $returnType->type;

            return $inner instanceof Node\Identifier && $inner->toString() === 'object';
        }

        if ($returnType instanceof Node\IntersectionType) {
            foreach ($returnType->types as $type) {
                if ($type instanceof Node\Identifier && $type->toString() === 'object') {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    private function returnTypeToString(Node $returnType): string
    {
        if ($returnType instanceof Node\Identifier || $returnType instanceof Node\Name) {
            return $returnType->toString();
        }

        if ($returnType instanceof Node\UnionType) {
            return implode('|', array_map($this->returnTypeToString(...), $returnType->types));
        }

        if ($returnType instanceof Node\NullableType) {
            return '?'.$this->returnTypeToString($returnType->type);
        }

        if ($returnType instanceof Node\IntersectionType) {
            return implode('&', array_map($this->returnTypeToString(...), $returnType->types));
        }

        return '(unknown)';
    }
}
