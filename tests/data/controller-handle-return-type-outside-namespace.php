<?php /** @noinspection ALL */

declare(strict_types=1);

namespace Some\Other\Place;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Routing\Attribute\Route;

class SomeController
{
    use HandleTrait;

    #[Route('/outside')]
    public function __invoke(): \stdClass
    {
        return $this->handle(new \stdClass());
    }
}
