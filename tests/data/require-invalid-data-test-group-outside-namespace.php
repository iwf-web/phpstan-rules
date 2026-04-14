<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Services;

class SomeService
{
    public function testLikeMethod(): void
    {
        $this->assertFailingValidation([]); // outside configured namespace — no error
    }

    private function assertFailingValidation(array $data): void {}
}
