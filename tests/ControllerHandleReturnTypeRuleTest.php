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

use IWF\PhpstanRules\Controller\ControllerHandleReturnTypeRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<ControllerHandleReturnTypeRule>
 *
 * @internal
 */
final class ControllerHandleReturnTypeRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerHandleReturnTypeRule(
            self::createReflectionProvider(),
            'App\Controller',
        );
    }

    public function testCorrectReturnTypes(): void
    {
        $files = [__DIR__.'/data/controller-handle-return-type.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrors($errors, [
            ['identifier' => ControllerHandleReturnTypeRule::IDENTIFIER, 'line' => 78],
            ['identifier' => ControllerHandleReturnTypeRule::IDENTIFIER, 'line' => 90],
        ]);
    }

    public function testOutsideNamespaceIsIgnored(): void
    {
        $files = [__DIR__.'/data/controller-handle-return-type-outside-namespace.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
