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

namespace IWF\RectorRules\Tests;

use IWF\RectorRules\Coala\Messenger\UseHandleBusTraitRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<UseHandleBusTraitRule>
 *
 * @internal
 */
final class UseHandleBusTraitRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new UseHandleBusTraitRule(
            self::createReflectionProvider(),
            handleBusTraitMappings: [
                'commandBus' => 'Coala\MessengerBundle\Messenger\HandleCommandBusTrait',
                'queryBus' => 'Coala\MessengerBundle\Messenger\HandleQueryBusTrait',
            ],
            handleBusTraitNamespaces: ['App\Controller'],
        );
    }

    public function testMissingTraits(): void
    {
        $files = [__DIR__.'/data/use-handle-bus-trait.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }

    public function testNoErrorsForCorrectCode(): void
    {
        $files = [__DIR__.'/data/use-handle-bus-trait-correct.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }

    public function testOutsideNamespaceIsIgnored(): void
    {
        $files = [__DIR__.'/data/use-handle-bus-trait-outside-namespace.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
