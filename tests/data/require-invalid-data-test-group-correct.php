<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Tests\ValidationCorrect;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

abstract class BaseTest
{
    protected function assertFailingValidation(array $data): void {}
}

class ValidationTest extends BaseTest
{
    #[Group('invalid-data-test')]
    public function testWithGroup(): void
    {
        $this->assertFailingValidation([]);
    }

    #[Group('invalid-data-test')]
    #[Test]
    public function withTestAttr(): void
    {
        $this->assertFailingValidation([]);
    }

    public function testNoAssert(): void
    {
        // no assertFailingValidation call
    }
}
