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

use IWF\PhpstanRules\Common\RequiredUseAliasGroupUseRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<RequiredUseAliasGroupUseRule>
 *
 * @internal
 */
final class RequiredUseAliasGroupUseRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequiredUseAliasGroupUseRule([
            ['namespace' => 'OpenApi\Attributes', 'alias' => 'OA'],
            ['namespace' => 'Coala\OpenApiContracts', 'alias' => 'CoalaOA'],
            ['namespace' => 'Coala\ModelMappingBundle\Annotation', 'alias' => 'ModelMapping'],
            ['namespace' => 'Symfony\Component\Validator\Constraints', 'alias' => 'Assert'],
            ['namespace' => 'Symfony\Component\Serializer\Attribute', 'alias' => 'Serializer'],
        ]);
    }

    public function testWrongAliases(): void
    {
        $files = [__DIR__.'/data/required-use-alias-group-use.php'];
        $errors = $this->gatherAnalyserErrors($files);
        self::assertRuleErrorsByAnnotation($errors, $files);
    }

    public function testCorrectAliases(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__.'/data/required-use-alias-group-use-correct.php']);
        self::assertNoRuleErrors($errors);
    }
}
