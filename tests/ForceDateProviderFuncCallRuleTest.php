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

use IWF\RectorRules\Coala\DateProvider\ForceDateProviderFuncCallRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ForceDateProviderFuncCallRule>
 *
 * @internal
 */
final class ForceDateProviderFuncCallRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForceDateProviderFuncCallRule(
            self::createReflectionProvider(),
        );
    }

    public function testBannedFunctionCalls(): void
    {
        $files = [__DIR__.'/data/force-date-provider-func-call.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }
}
