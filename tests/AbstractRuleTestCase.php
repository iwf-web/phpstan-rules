<?php

declare(strict_types=1);

namespace Coala\TestingBundle\Tests\PHPStan\Rules;

use PHPStan\Analyser\Error;
use PHPStan\Testing\RuleTestCase;

/**
 * @template TRule of \PHPStan\Rules\Rule
 *
 * @extends RuleTestCase<TRule>
 */
abstract class AbstractRuleTestCase extends RuleTestCase
{
    /**
     * Asserts that the given files produce exactly the expected errors,
     * matched by identifier and line number only (ignoring message text).
     *
     * @param list<Error>                                  $actualErrors
     * @param list<array{identifier: string, line: int}> $expectedErrors
     */
    protected static function assertRuleErrors(array $actualErrors, array $expectedErrors): void
    {
        $actual = array_map(
            static fn (Error $error): array => [
                'identifier' => $error->getIdentifier() ?? '',
                'line' => $error->getLine() ?? -1,
            ],
            $actualErrors,
        );

        $sort = static fn (array $a, array $b): int => $a['line'] <=> $b['line'];
        usort($actual, $sort);
        usort($expectedErrors, $sort);

        self::assertSame($expectedErrors, $actual);
    }

    /**
     * Asserts that the given files produce no errors.
     *
     * @param list<Error> $actualErrors
     */
    protected static function assertNoRuleErrors(array $actualErrors): void
    {
        self::assertRuleErrors($actualErrors, []);
    }
}
