<?php

declare(strict_types=1);

namespace Coala\TestingBundle\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Disallows using legacy Symfony Annotation-namespace classes as PHP attributes.
 *
 * Symfony has migrated all annotations to \Attribute\ namespaces. Using the
 * legacy \Annotation\ variant as a PHP 8 attribute should be replaced.
 *
 * @implements Rule<Node\Attribute>
 */
final class NoAnnotationAsAttributeRule implements Rule
{
    public const IDENTIFIER = 'coala.noAnnotationAsAttribute';
    private const SYMFONY_PREFIX = 'Symfony\\';

    public function getNodeType(): string
    {
        return Node\Attribute::class;
    }

    /**
     * @param Node\Attribute $node
     *
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $name = $node->name->toString();

        if (!str_starts_with($name, self::SYMFONY_PREFIX)) {
            return [];
        }

        if (!str_contains($name, '\\Annotation\\')) {
            return [];
        }

        $suggested = str_replace('\\Annotation\\', '\\Attribute\\', $name);
        $message = sprintf(
            'Attribute #[%s] uses a legacy Annotation namespace. Use #[%s] instead.',
            $name,
            $suggested,
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->line($node->getStartLine())
                ->tip(sprintf('Replace "use %s;" with "use %s;" in your imports.', $name, $suggested))
                ->build(),
        ];
    }
}
