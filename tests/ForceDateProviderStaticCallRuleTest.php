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

use IWF\PhpstanRules\Coala\DateProvider\ForceDateProviderStaticCallRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ForceDateProviderStaticCallRule>
 *
 * @internal
 */
final class ForceDateProviderStaticCallRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForceDateProviderStaticCallRule(
            self::createReflectionProvider(),
        );
    }

    public function testBannedStaticCalls(): void
    {
        $files = [__DIR__.'/data/force-date-provider-static-call.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }
}
