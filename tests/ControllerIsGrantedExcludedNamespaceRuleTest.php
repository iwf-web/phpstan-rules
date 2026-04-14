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

use IWFWeb\PhpstanRules\Controller\ControllerIsGrantedRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ControllerIsGrantedRule>
 *
 * @internal
 */
final class ControllerIsGrantedExcludedNamespaceRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerIsGrantedRule(
            'App\Controller',
            excludedNamespaces: ['App\Controller\Api\Public'],
        );
    }

    public function testExcludedNamespaceIsIgnored(): void
    {
        $files = [__DIR__.'/data/controller-is-granted-excluded-namespace.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
