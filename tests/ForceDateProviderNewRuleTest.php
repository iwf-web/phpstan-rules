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

use IWFWeb\PhpstanRules\Coala\DateProvider\ForceDateProviderNewRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ForceDateProviderNewRule>
 *
 * @internal
 */
final class ForceDateProviderNewRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForceDateProviderNewRule(
            self::createReflectionProvider(),
        );
    }

    public function testRelativeAndNoArgErrors(): void
    {
        $files = [__DIR__.'/data/force-date-provider-new.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }
}
