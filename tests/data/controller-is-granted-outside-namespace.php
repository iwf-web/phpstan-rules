<?php /** @noinspection ALL */ declare(strict_types=1);

namespace Other\Namespace;

use Symfony\Component\Routing\Attribute\Route;

class SomeOtherClass
{
    #[Route('/outside')]
    public function __invoke(): void
    {
    }
}
