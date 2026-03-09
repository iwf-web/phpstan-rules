<?php

declare(strict_types=1);

namespace Coala\TestingBundle\Tests\PHPStan\Rules;

use Coala\TestingBundle\PHPStan\Rules\ControllerIsGrantedRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ControllerIsGrantedRule>
 */
class ControllerIsGrantedRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerIsGrantedRule(
            'App\\Controller',
            excludedControllers: ['App\\Controller\\Api\\Security\\LoginController'],
        );
    }

    public function testMissingIsGranted(): void
    {
        $files = [__DIR__ . '/data/controller-is-granted.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrors($errors, [
            ['identifier' => ControllerIsGrantedRule::IDENTIFIER, 'line' => 30],
            ['identifier' => ControllerIsGrantedRule::IDENTIFIER, 'line' => 44],
        ]);
    }

    public function testExcludedControllerIsIgnored(): void
    {
        $files = [__DIR__ . '/data/controller-is-granted-excluded.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }

    public function testAbstractControllerIsIgnored(): void
    {
        $files = [__DIR__ . '/data/controller-is-granted-abstract.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
