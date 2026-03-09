<?php

declare(strict_types=1);

namespace Coala\TestingBundle\Tests\PHPStan\Rules;

use Coala\TestingBundle\PHPStan\Rules\NoAnnotationAsAttributeRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoAnnotationAsAttributeRule>
 */
class NoAnnotationAsAttributeRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoAnnotationAsAttributeRule();
    }

    public function testLegacyAnnotationNamespace(): void
    {
        $files = [__DIR__ . '/data/no-annotation-as-attribute.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrors($errors, [
            ['identifier' => NoAnnotationAsAttributeRule::IDENTIFIER, 'line' => 10],
        ]);
    }

    public function testCorrectAttributeNamespace(): void
    {
        $files = [__DIR__ . '/data/no-annotation-as-attribute-correct.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
