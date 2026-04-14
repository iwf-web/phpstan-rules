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
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Disallows creating DateTime/DateTimeImmutable with no argument or a relative date string.
 *
 * @implements Rule<New_>
 */
final class ForceDateProviderNewRule implements Rule
{
    public const string IDENTIFIER = 'iwfWeb.forceDateProviderNew';
    private const string SENTINEL_CLASS = 'Coala\DateProviderBundle\Service\DateProvider\DateProviderInterface';
    private const array TARGET_CLASSES = ['DateTime', 'DateTimeImmutable'];

    /**
     * @param list<string> $allowedFormats
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly array $allowedFormats = ['Y-m-d', 'Y-m-d H:i:s', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i:sP', 'U'],
    ) {}

    #[\Override]
    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $node
     *
     * @return list<IdentifierRuleError>
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

        if (!\in_array($node->class->getLast(), self::TARGET_CLASSES, true)) {
            return [];
        }

        if ($node->args === []) {
            return [$this->buildError($node->getStartLine())];
        }

        $firstArg = $node->args[0];
        if (!$firstArg instanceof Node\Arg) {
            return [];
        }

        if ($firstArg->value instanceof Node\Scalar\String_) {
            if (!$this->isAbsoluteDateString($firstArg->value->value)) {
                return [$this->buildError($node->getStartLine())];
            }

            return [];
        }

        return [];
    }

    private function isAbsoluteDateString(string $value): bool
    {
        foreach ($this->allowedFormats as $format) {
            if (\DateTimeImmutable::createFromFormat($format, $value) !== false) {
                return true;
            }
        }

        return false;
    }

    private function buildError(int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message('Avoid creating DateTime/DateTimeImmutable with a relative or missing argument.')
            ->identifier(self::IDENTIFIER)
            ->line($line)
            ->tip('Use DateProviderInterface to obtain the current time.')
            ->build()
        ;
    }
}
