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

namespace IWF\RectorRules\Coala\Messenger;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * In configured namespaces, if a class defines a set{Key} method it must use the matching
 * Coala handle-bus trait instead of defining the setter manually.
 *
 * @implements Rule<Class_>
 */
final class UseHandleBusTraitRule implements Rule
{
    public const string IDENTIFIER = 'iwf.useHandleBusTrait';
    private const string SENTINEL_CLASS = 'Coala\MessengerBundle\Messenger\HandleQueryBusTrait';

    /**
     * @param array<string, string> $handleBusTraitMappings  key => traitFQCN
     * @param list<string>          $handleBusTraitNamespaces
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly array $handleBusTraitMappings = [],
        private readonly array $handleBusTraitNamespaces = ['App\\Controller'],
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
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->reflectionProvider->hasClass(self::SENTINEL_CLASS)) {
            return [];
        }

        if ($this->handleBusTraitMappings === []) {
            return [];
        }

        $namespace = $scope->getNamespace();
        if ($namespace === null) {
            return [];
        }

        $matchesNamespace = false;
        foreach ($this->handleBusTraitNamespaces as $prefix) {
            if (str_starts_with($namespace, $prefix)) {
                $matchesNamespace = true;
                break;
            }
        }

        if (!$matchesNamespace) {
            return [];
        }

        $usedTraits = $this->collectUsedTraits($node);

        $errors = [];

        foreach ($this->handleBusTraitMappings as $key => $traitFqcn) {
            $setterName = 'set'.ucfirst((string) $key);

            if (!$this->classDeclaresMethod($node, $setterName)) {
                continue;
            }

            if (\in_array($traitFqcn, $usedTraits, true)) {
                continue;
            }

            $shortTrait = substr($traitFqcn, (int) strrpos($traitFqcn, '\\') + 1);

            $errors[] = RuleErrorBuilder::message(
                \sprintf('Method %s requires use of %s.', $setterName, $shortTrait),
            )
                ->identifier(self::IDENTIFIER)
                ->line($node->getStartLine())
                ->build();
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function collectUsedTraits(Class_ $node): array
    {
        $traits = [];

        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof TraitUse)) {
                continue;
            }

            foreach ($stmt->traits as $trait) {
                $traits[] = $trait->toString();
            }
        }

        return $traits;
    }

    private function classDeclaresMethod(Class_ $node, string $methodName): bool
    {
        foreach ($node->getMethods() as $method) {
            if ($method->name->toString() === $methodName) {
                return true;
            }
        }

        return false;
    }
}
