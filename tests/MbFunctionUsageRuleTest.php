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

use IWFWeb\PhpstanRules\Common\MbFunctionUsageRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<MbFunctionUsageRule>
 *
 * @internal
 */
final class MbFunctionUsageRuleTest extends AbstractRuleTestCase
{
    #[\Override]
    protected function getRule(): Rule
    {
        return new MbFunctionUsageRule();
    }

    public function testNonMbFunction(): void
    {
        $files = [__DIR__.'/data/non-mb-functions.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }
}
