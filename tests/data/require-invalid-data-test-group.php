<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Tests\Validation;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

abstract class BaseTest
{
    protected function assertFailingValidation(array $data): void {}
}

class ValidationTest extends BaseTest
{
    public function testMissingGroup(): void // @error iwfWeb.requireInvalidDataTestGroup
    {
        $this->assertFailingValidation([]);
    }

    #[Test] // @error iwfWeb.requireInvalidDataTestGroup
    public function missingGroup(): void
    {
        $this->assertFailingValidation([]);
    }

    #[Group('invalid-data-test')]
    public function testWithGroup(): void
    {
        $this->assertFailingValidation([]);
    }

    public function testMissingGroupWithTestAttr(): void // @error iwfWeb.requireInvalidDataTestGroup
    {
        $this->assertFailingValidation([]);
    }

    #[Test] // @error iwfWeb.requireInvalidDataTestGroup
    public function missingGroupWithTestAttr(): void
    {
        $this->assertFailingValidation([]);
    }

    public function testNoAssertCall(): void
    {
        // does not call assertFailingValidation
    }

    private function helperMethod(): void
    {
        $this->assertFailingValidation([]);
    }
}
