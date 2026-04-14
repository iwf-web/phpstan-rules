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

namespace IWFWeb\PhpstanRules\Coala\DateProvider;

use Coala\DateProviderBundle\Service\DateProvider\DateProviderInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Disallows DateTime/DateTimeImmutable::createFromFormat() with a relative date string.
 *
 * @implements Rule<StaticCall>
 */
final readonly class ForceDateProviderStaticCallRule implements Rule
{
    public const string IDENTIFIER = 'iwfWeb.forceDateProviderStaticCall';
    private const string SENTINEL_CLASS = DateProviderInterface::class;
    private const array TARGET_CLASSES = ['DateTime', 'DateTimeImmutable'];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    #[\Override]
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
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

        if (!$node->class instanceof Node\Name) {
            return [];
        }

        if (!$node->name instanceof Node\Identifier) {
            return [];
        }

        if (!\in_array($node->class->getLast(), self::TARGET_CLASSES, true)) {
            return [];
        }

        if ($node->name->toString() !== 'createFromFormat') {
            return [];
        }

        // createFromFormat($format, $datetime, ...) — need both first two args as string literals
        if (\count($node->args) < 2) {
            return [];
        }

        [$formatArg, $valueArg] = $node->args;

        if (!($formatArg instanceof Node\Arg) || !($valueArg instanceof Node\Arg)) {
            return [];
        }

        if (!$formatArg->value instanceof Node\Scalar\String_) {
            return [];
        }

        if (!$valueArg->value instanceof Node\Scalar\String_) {
            return [];
        }

        $format = $formatArg->value->value;
        $value = $valueArg->value->value;

        if (\DateTimeImmutable::createFromFormat($format, $value) !== false) {
            return [];
        }

        $message = \sprintf(
            'Avoid passing a relative date string "%s" to %s::createFromFormat(); use DateProviderInterface to obtain the current time.',
            $value,
            $node->class->getLast(),
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->line($node->getStartLine())
                ->tip('Use DateProviderInterface to obtain the current time.')
                ->build(),
        ];
    }
}
