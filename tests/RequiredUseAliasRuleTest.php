<?php

declare(strict_types=1);

namespace Coala\TestingBundle\Tests\PHPStan\Rules;

use Coala\TestingBundle\PHPStan\Rules\RequiredUseAliasRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<RequiredUseAliasRule>
 */
class RequiredUseAliasRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequiredUseAliasRule([
            ['namespace' => 'OpenApi\Attributes', 'alias' => 'OA'],
            ['namespace' => 'Coala\OpenApiContracts', 'alias' => 'CoalaOA'],
            ['namespace' => 'Coala\ModelMappingBundle\Annotation', 'alias' => 'ModelMapping'],
            ['namespace' => 'Symfony\Component\Validator\Constraints', 'alias' => 'Assert'],
            ['namespace' => 'Symfony\Component\Serializer\Attribute', 'alias' => 'Serializer'],
        ]);
    }

    public function testWrongAliases(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/required-use-alias.php']);
        self::assertRuleErrors($errors, [
            ['identifier' => RequiredUseAliasRule::IDENTIFIER, 'line' => 7],
            ['identifier' => RequiredUseAliasRule::IDENTIFIER, 'line' => 8],
            ['identifier' => RequiredUseAliasRule::IDENTIFIER, 'line' => 9],
        ]);
    }

    public function testCorrectAliases(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/required-use-alias-correct.php']);
        self::assertNoRuleErrors($errors);
    }
}
