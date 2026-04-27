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

use App\Controller\Api\Security\LoginController;
use IWFWeb\PhpstanRules\Common\AttributeRequirementsRule;
use PHPStan\Rules\Rule;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractRuleTestCase<AttributeRequirementsRule>
 *
 * @internal
 */
final class AttributeRequirementsRuleExcludedClassesTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new AttributeRequirementsRule(
            attributeDefinitions: [
                [
                    'attribute' => Route::class,
                    'requires' => [
                        'OpenApi\Attributes\Tag',
                        'Symfony\Component\Security\Http\Attribute\IsGranted',
                    ],
                ],
            ],
            excludedClasses: [LoginController::class],
        );
    }

    public function testExcludedClassIsIgnored(): void
    {
        $files = [__DIR__.'/data/attribute-requirements-excluded.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertNoRuleErrors($errors);
    }
}
