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

namespace IWF\PhpstanRules\Common;

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
        return Node\Stmt\GroupUse::class;
    }

    /**
     * @param Node\Stmt\GroupUse $node
     *
     * @return list<RuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
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

            $fqn = $node->prefix->toString().'\\'.$use->name->toString();
            $error = $this->checkUseItem($use, $fqn);

            if ($error !== null) {
                $errors[] = $error;
            }
        }

        return $errors;
    }
}
