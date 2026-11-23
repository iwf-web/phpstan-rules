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

namespace IWFWeb\PhpstanRules\Tests;

use IWFWeb\PhpstanRules\Common\DeclareStrictTypesRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<DeclareStrictTypesRule>
 *
 * @internal
 */
final class DeclareStrictTypesRuleTest extends AbstractRuleTestCase
{
    #[\Override]
    protected function getRule(): Rule
    {
        return new DeclareStrictTypesRule();
    }

    public function testMissingOrInvalidDeclare(): void
    {
        $files = [
            __DIR__.'/data/declare-strict-types-missing.php',
            __DIR__.'/data/declare-strict-types-zero.php',
            __DIR__.'/data/declare-strict-types-shebang.php',
        ];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }

    public function testCorrectDeclare(): void
    {
        $errors = $this->gatherAnalyserErrors([
            __DIR__.'/data/declare-strict-types-correct.php',
            __DIR__.'/data/declare-strict-types-combined-correct.php',
            __DIR__.'/data/declare-strict-types-shebang-correct.php',
            __DIR__.'/data/declare-strict-types-uppercase-correct.php',
        ]);
        self::assertNoRuleErrors($errors);
    }
}
