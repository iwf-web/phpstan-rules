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

use IWFWeb\PhpstanRules\Coala\RestControl\SerializerIsGrantedSubjectIgnoreRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<SerializerIsGrantedSubjectIgnoreRule>
 *
 * @internal
 */
final class SerializerIsGrantedSubjectIgnoreRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new SerializerIsGrantedSubjectIgnoreRule(self::createReflectionProvider());
    }

    public function testUnprotectedSubjectProperties(): void
    {
        $files = [__DIR__.'/data/serializer-is-granted-subject-ignore.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }

    public function testNoErrorsForCorrectCode(): void
    {
        $files = [__DIR__.'/data/serializer-is-granted-subject-ignore-correct.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
