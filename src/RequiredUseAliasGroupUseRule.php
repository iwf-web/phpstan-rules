<?php

declare(strict_types=1);

namespace Coala\TestingBundle\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\ShouldNotHappenException;

/**
 * Enforces that certain namespaces are imported with a specific alias in grouped use statements.
 *
 * @implements Rule<Node\Stmt\GroupUse>
 */
final class RequiredUseAliasGroupUseRule implements Rule
{
    use RequiredUseAliasMatcherTrait;

    public const IDENTIFIER = 'coala.requiredUseAlias';

    /**
     * @param list<array{namespace: string, alias: string}> $requiredUseAliases
     */
    public function __construct(array $requiredUseAliases)
    {
        $this->aliasByNamespace = $this->buildAliasByNamespace($requiredUseAliases);
    }

    public function getNodeType(): string
    {
        return Node\Stmt\GroupUse::class;
    }

    /**
     * @param Node\Stmt\GroupUse $node
     *
     * @return list<RuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach ($node->uses as $use) {
            $effectiveType = $use->type !== Node\Stmt\Use_::TYPE_UNKNOWN
                ? $use->type
                : $node->type;

            if ($effectiveType !== Node\Stmt\Use_::TYPE_NORMAL) {
                continue;
            }

            $fqn = $node->prefix->toString() . '\\' . $use->name->toString();
            $error = $this->checkUseItem($use, $fqn);

            if ($error !== null) {
                $errors[] = $error;
            }
        }

        return $errors;
    }
}
