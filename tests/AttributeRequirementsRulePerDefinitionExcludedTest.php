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

use App\Controller\Api\PerDefinition\ExcludedForRouteController;
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
final class AttributeRequirementsRulePerDefinitionExcludedTest extends AbstractRuleTestCase
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
                    'excludedClasses' => [ExcludedForRouteController::class],
                ],
            ],
        );
    }

    public function testPerDefinitionExclusionSkipsOnlyTheExcludedClass(): void
    {
        $files = [__DIR__.'/data/attribute-requirements-per-definition-excluded.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }
}
