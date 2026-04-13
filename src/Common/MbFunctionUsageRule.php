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
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Disallows using string related functions that have an mb counterpart.
 *
 * @implements Rule<Node\Expr\FuncCall>
 */
class MbFunctionUsageRule implements Rule
{
    public const string IDENTIFIER = 'iwf.mbFunctionUsageRule';

    #[\Override]
    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @return list<IdentifierRuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Name) {
            return [];
        }

        $name = $node->name->toString();

        if (!\in_array($name, [
            'chr',
            'ord',
            'parse_str',
            'str_pad',
            'str_split',
            'stripos',
            'stristr',
            'strlen',
            'strpos',
            'strrchr',
            'strripos',
            'strrpos',
            'strstr',
            'strtolower',
            'strtoupper',
            'substr',
            'substr_count',
        ], true)) {
            return [];
        }

        $message = \sprintf(
            'Function "%s" might not behave as expected when multibyte input is entered.',
            $name,
        );

        $tip = \sprintf(
            'Replace "%1$s" with its multibyte counterpart "mb_%1$s".',
            $name,
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->line($node->getStartLine())
                ->tip($tip)
                ->build(),
        ];
    }
}
