<?php

declare(strict_types=1);

namespace Coala\TestingBundle\Tests\PHPStan\Rules;

use Coala\TestingBundle\PHPStan\Rules\ControllerHandleReturnTypeRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ControllerHandleReturnTypeRule>
 */
class ControllerHandleReturnTypeRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerHandleReturnTypeRule(
            self::createReflectionProvider(),
            'App\\Controller',
        );
    }

    public function testCorrectReturnTypes(): void
    {
        $files = [__DIR__ . '/data/controller-handle-return-type.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrors($errors, [
            ['identifier' => ControllerHandleReturnTypeRule::IDENTIFIER, 'line' => 80],
            ['identifier' => ControllerHandleReturnTypeRule::IDENTIFIER, 'line' => 92],
        ]);
    }

    public function testOutsideNamespaceIsIgnored(): void
    {
        $files = [__DIR__ . '/data/controller-handle-return-type-outside-namespace.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
