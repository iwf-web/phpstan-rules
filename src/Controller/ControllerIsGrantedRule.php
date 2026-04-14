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

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Ensures that every controller method with a #[Route] attribute also has a
 * #[IsGranted] attribute (fully-qualified) on either the method or the class.
 *
 * @implements Rule<Class_>
 */
final class ControllerIsGrantedRule implements Rule
{
    use ControllerRuleHelperTrait;

    public const string IDENTIFIER = 'iwf.controllerMissingIsGranted';
    private const string IS_GRANTED_ATTRIBUTE = 'Symfony\Component\Security\Http\Attribute\IsGranted';

    /**
     * @param list<string> $excludedNamespaces  Namespace prefixes to skip
     * @param list<string> $excludedControllers Fully-qualified class names to skip
     */
    public function __construct(
        private readonly string $controllerNamespace = 'App\Controller',
        private readonly array $excludedNamespaces = [],
        private readonly array $excludedControllers = [],
    ) {}

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
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name === null) {
            return [];
        }

        if ($node->isAbstract()) {
            return [];
        }

        $namespace = $scope->getNamespace();
        if ($namespace === null || !str_starts_with($namespace, $this->controllerNamespace)) {
            return [];
        }

        $fqcn = $namespace.'\\'.$node->name->toString();

        if ($this->isExcluded($fqcn)) {
            return [];
        }

        if ($this->hasIsGrantedAttribute($node->attrGroups)) {
            return [];
        }

        $errors = [];

        foreach ($node->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }

            if (!$this->hasRouteAttribute($method)) {
                continue;
            }

            if ($this->hasIsGrantedAttribute($method->attrGroups)) {
                continue;
            }

            $message = \sprintf(
                'Controller method %s::%s() has a #[Route] attribute but no #[IsGranted] attribute.',
                $node->name->toString(),
                $method->name->toString(),
            );

            $errors[] = RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->line($method->getStartLine())
                ->tip('Add #[IsGranted(\'PERMISSION_KEY\')] to the method or class to enforce authorization.')
                ->build()
            ;
        }

        return $errors;
    }

    /**
     * @param array<Node\AttributeGroup> $attrGroups
     */
    private function hasIsGrantedAttribute(array $attrGroups): bool
    {
        foreach ($attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === self::IS_GRANTED_ATTRIBUTE) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isExcluded(string $fqcn): bool
    {
        if (\in_array($fqcn, $this->excludedControllers, true)) {
            return true;
        }

        foreach ($this->excludedNamespaces as $ns) {
            if (str_starts_with($fqcn, $ns)) {
                return true;
            }
        }

        return false;
    }
}
