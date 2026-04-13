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

namespace IWF\RectorRules\Tests;

use IWF\RectorRules\Coala\Testing\RequireInvalidDataTestGroupRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<RequireInvalidDataTestGroupRule>
 *
 * @internal
 */
final class RequireInvalidDataTestGroupRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequireInvalidDataTestGroupRule(
            self::createReflectionProvider(),
            requireInvalidDataTestGroupNamespaces: ['App\Tests'],
        );
    }

    public function testMissingGroupAttribute(): void
    {
        $files = [__DIR__.'/data/require-invalid-data-test-group.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }

    public function testNoErrorsForCorrectCode(): void
    {
        $files = [__DIR__.'/data/require-invalid-data-test-group-correct.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
