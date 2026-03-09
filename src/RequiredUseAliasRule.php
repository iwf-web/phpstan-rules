<?php

declare(strict_types=1);

namespace Coala\TestingBundle\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\ShouldNotHappenException;

/**
 * Enforces that certain namespaces are imported with a specific alias.
 *
 * @implements Rule<Node\Stmt\Use_>
 */
final class RequiredUseAliasRule implements Rule
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
        return Node\Stmt\Use_::class;
    }

    /**
     * @param Node\Stmt\Use_ $node
     *
     * @return list<RuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->type !== Node\Stmt\Use_::TYPE_NORMAL) {
            return [];
        }

        $errors = [];

        foreach ($node->uses as $use) {
            $error = $this->checkUseItem($use, $use->name->toString());

            if ($error !== null) {
                $errors[] = $error;
            }
        }

        return $errors;
    }
}
