<?php

declare(strict_types=1);

namespace Coala\TestingBundle\Tests\PHPStan\Rules;

use Coala\TestingBundle\PHPStan\Rules\RequiredUseAliasGroupUseRule;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<RequiredUseAliasGroupUseRule>
 */
class RequiredUseAliasGroupUseRuleTest extends AbstractRuleTestCase
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
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/required-use-alias-group-use.php']);
        self::assertRuleErrors($errors, [
            ['identifier' => RequiredUseAliasGroupUseRule::IDENTIFIER, 'line' => 8],
            ['identifier' => RequiredUseAliasGroupUseRule::IDENTIFIER, 'line' => 11],
        ]);
    }

    public function testCorrectAliases(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/required-use-alias-group-use-correct.php']);
        self::assertNoRuleErrors($errors);
    }
}
