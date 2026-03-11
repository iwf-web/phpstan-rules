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
    public function testMissingGroup(): void // @error iwf.requireInvalidDataTestGroup
    {
        $this->assertFailingValidation([]);
    }

    #[Group('invalid-data-test')]
    public function testWithGroup(): void
    {
        $this->assertFailingValidation([]);
    }

    #[Test] // @error iwf.requireInvalidDataTestGroup
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
