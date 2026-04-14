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

namespace IWF\PhpstanRules\Coala\DateProvider;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Disallows calling time/date/mktime/strtotime/gmdate functions; use DateProviderInterface instead.
 *
 * @implements Rule<FuncCall>
 */
final class ForceDateProviderFuncCallRule implements Rule
{
    public const string IDENTIFIER = 'iwf.forceDateProviderFuncCall';
    private const string SENTINEL_CLASS = 'Coala\DateProviderBundle\Service\DateProvider\DateProviderInterface';
    private const array BANNED_FUNCTIONS = ['time', 'date', 'mktime', 'strtotime', 'gmdate'];

    /**
     * These functions accept an explicit timestamp as their second argument.
     * When provided, they format/operate on a known point in time rather than "now".
     */
    private const array TIMESTAMP_PARAM_FUNCTIONS = ['date', 'gmdate', 'strtotime'];

    /**
     * These functions only implicitly use "now" when called with no arguments.
     * Any argument means the caller is constructing a specific point in time.
     */
    private const array REQUIRES_ARGS_FUNCTIONS = ['mktime'];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    #[\Override]
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $node
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

        if (!$node->name instanceof Node\Name) {
            return [];
        }

        $funcName = $node->name->getLast();

        if (!\in_array($funcName, self::BANNED_FUNCTIONS, true)) {
            return [];
        }

        if (
            \in_array($funcName, self::TIMESTAMP_PARAM_FUNCTIONS, true)
            && \count($node->args) >= 2
        ) {
            return [];
        }

        if (
            \in_array($funcName, self::REQUIRES_ARGS_FUNCTIONS, true)
            && \count($node->args) >= 1
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    'Avoid calling %s(); use DateProviderInterface to obtain the current time.',
                    $funcName,
                ),
            )
                ->identifier(self::IDENTIFIER)
                ->line($node->getStartLine())
                ->tip('Use DateProviderInterface to obtain the current time.')
                ->build(),
        ];
    }
}
