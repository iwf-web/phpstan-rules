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

use IWFWeb\PhpstanRules\Coala\Messenger\UseHandleBusTraitRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<UseHandleBusTraitRule>
 *
 * @internal
 */
final class UseHandleBusTraitRuleEmptyMappingsTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new UseHandleBusTraitRule(
            self::createReflectionProvider(),
            handleBusTraitMappings: [],
            handleBusTraitNamespaces: ['App\Controller'],
        );
    }

    public function testEmptyMappingsProducesNoErrors(): void
    {
        $files = [__DIR__.'/data/use-handle-bus-trait.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
