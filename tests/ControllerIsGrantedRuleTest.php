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

use IWF\PhpstanRules\Controller\ControllerIsGrantedRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ControllerIsGrantedRule>
 *
 * @internal
 */
final class ControllerIsGrantedRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerIsGrantedRule(
            'App\Controller',
            excludedControllers: ['App\Controller\Api\Security\LoginController'],
        );
    }

    public function testMissingIsGranted(): void
    {
        $files = [__DIR__.'/data/controller-is-granted.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }

    public function testExcludedControllerIsIgnored(): void
    {
        $files = [__DIR__.'/data/controller-is-granted-excluded.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }

    public function testAbstractControllerIsIgnored(): void
    {
        $files = [__DIR__.'/data/controller-is-granted-abstract.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
