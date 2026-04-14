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

use IWF\PhpstanRules\Common\NoAnnotationAsAttributeRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoAnnotationAsAttributeRule>
 *
 * @internal
 */
final class NoAnnotationAsAttributeRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoAnnotationAsAttributeRule();
    }

    public function testLegacyAnnotationNamespace(): void
    {
        $files = [__DIR__.'/data/no-annotation-as-attribute.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrors($errors, [
            ['identifier' => NoAnnotationAsAttributeRule::IDENTIFIER, 'line' => 8],
        ]);
    }

    public function testCorrectAttributeNamespace(): void
    {
        $files = [__DIR__.'/data/no-annotation-as-attribute-correct.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
