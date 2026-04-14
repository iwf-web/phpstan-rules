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

namespace IWF\PhpstanRules\Tests;

use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @template TRule of Rule
 *
 * @extends RuleTestCase<TRule>
 */
abstract class AbstractRuleTestCase extends RuleTestCase
{
    /**
     * @return list<string>
     */
    /**
     * Asserts that the given files produce exactly the expected errors,
     * matched by identifier and line number only (ignoring message text).
     *
     * @param list<Error>                                $actualErrors
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

        self::assertSame($expectedErrors, $actual, 'Failed asserting that all expected errors match.');
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

    /**
     * Reads @error annotations from test data files and asserts that actual errors match.
     *
     * Place one or more `@error identifier` tags in a line's comment to declare expected errors:
     *
     *   $x = new DateTime();  // @error iwf.forceDateProviderNew
     *   #[Route('/foo')]      // @error iwf.attributeRequirements @error iwf.attributeRequirements
     *
     * @param list<Error>  $actualErrors
     * @param list<string> $files
     */
    protected static function assertRuleErrorsByAnnotation(array $actualErrors, array $files): void
    {
        $expectedErrors = [];

        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES);

            if ($lines === false) {
                throw new \RuntimeException("Could not read file: {$file}");
            }

            foreach ($lines as $lineIndex => $line) {
                preg_match_all('/@error (\S+)/', $line, $matches);

                foreach ($matches[1] as $identifier) {
                    $expectedErrors[] = [
                        'identifier' => $identifier,
                        'line' => $lineIndex + 1,
                    ];
                }
            }
        }

        self::assertRuleErrors($actualErrors, $expectedErrors);
    }
}
