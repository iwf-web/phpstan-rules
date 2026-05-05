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

use App\Controller\Api\Combined\GloballyExcludedController;
use App\Controller\Api\Combined\PerDefinitionExcludedController;
use IWFWeb\PhpstanRules\Common\AttributeRequirementsRule;
use OpenApi\Attributes\Tag;
use PHPStan\Rules\Rule;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractRuleTestCase<AttributeRequirementsRule>
 *
 * @internal
 */
final class AttributeRequirementsRuleCombinedExclusionTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new AttributeRequirementsRule(
            attributeDefinitions: [
                [
                    'attribute' => Route::class,
                    'requires' => [
                        Tag::class,
                        IsGranted::class,
                    ],
                    'excludedClasses' => [PerDefinitionExcludedController::class],
                ],
            ],
            excludedClasses: [GloballyExcludedController::class],
        );
    }

    public function testGlobalAndPerDefinitionExclusionsBothApply(): void
    {
        $files = [__DIR__.'/data/attribute-requirements-combined-excluded.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }
}
