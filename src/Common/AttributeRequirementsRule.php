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

namespace IWFWeb\PhpstanRules\Common;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Enforces that methods carrying a trigger attribute also carry all required companion attributes.
 *
 * @implements Rule<ClassMethod>
 */
final readonly class AttributeRequirementsRule implements Rule
{
    public const string IDENTIFIER = 'iwfWeb.attributeRequirements';

    /**
     * @param list<array{attribute: string, requires: list<string>}> $attributeDefinitions
     * @param list<string>                                           $excludedClasses      Fully-qualified class names to skip
     */
    public function __construct(
        private array $attributeDefinitions = [],
        private array $excludedClasses = [],
    ) {}

    #[\Override]
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     *
     * @return list<IdentifierRuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->attributeDefinitions === []) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection !== null && \in_array($classReflection->getName(), $this->excludedClasses, true)) {
            return [];
        }

        $presentAttributes = [];

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $presentAttributes[] = $attr->name->toString();
            }
        }

        $errors = [];

        foreach ($this->attributeDefinitions as $requirement) {
            $triggerAttribute = $requirement['attribute'];

            if (!\in_array($triggerAttribute, $presentAttributes, true)) {
                continue;
            }

            foreach ($requirement['requires'] as $requiredAttribute) {
                if (!\in_array($requiredAttribute, $presentAttributes, true)) {
                    $message = \sprintf(
                        'Method with attribute #[%s] must also carry attribute #[%s].',
                        $triggerAttribute,
                        $requiredAttribute,
                    );
                    $errors[] = RuleErrorBuilder::message($message)
                        ->identifier(self::IDENTIFIER)
                        ->line($node->getStartLine())
                        ->build()
                    ;
                }
            }
        }

        return $errors;
    }
}
