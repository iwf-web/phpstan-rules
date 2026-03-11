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

namespace IWF\RectorRules\Common;

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

    public const string IDENTIFIER = 'iwf.requiredUseAlias';

    /**
     * @param list<array{namespace: string, alias: string}> $aliasDefinitions
     */
    public function __construct(array $aliasDefinitions)
    {
        $this->aliasByNamespace = $this->buildAliasByNamespace($aliasDefinitions);
    }

    #[\Override]
    public function getNodeType(): string
    {
        return Node\Stmt\Use_::class;
    }

    /**
     * @param Node\Stmt\Use_ $node
     *
     * @return list<RuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
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
