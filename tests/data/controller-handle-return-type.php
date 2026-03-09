<?php /** @noinspection ALL */

declare(strict_types=1);

namespace App\Controller\HandleReturnType;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Routing\Attribute\Route;

class CorrectObjectReturn
{
    use HandleTrait;

    #[Route('/correct')]
    public function __invoke(): object
    {
        return $this->handle(new \stdClass());
    }
}

class CorrectMixedReturn
{
    use HandleTrait;

    #[Route('/mixed')]
    public function __invoke(): mixed
    {
        return $this->handle(new \stdClass());
    }
}

class CorrectNullableObject
{
    use HandleTrait;

    #[Route('/nullable')]
    public function __invoke(): ?object
    {
        return $this->handle(new \stdClass());
    }
}

class CorrectNoRoute
{
    use HandleTrait;

    public function doSomething(): \stdClass
    {
        return $this->handle(new \stdClass());
    }
}

class CorrectNoHandleTrait
{
    #[Route('/no-handle')]
    public function __invoke(): \stdClass
    {
        return new \stdClass();
    }
}

class CorrectIntersectionWithObject
{
    use HandleTrait;

    #[Route('/intersection')]
    public function __invoke(): object&\Countable
    {
        return $this->handle(new \stdClass());
    }
}

class IncorrectSpecificReturn
{
    use HandleTrait;

    #[Route('/bad')]
    public function __invoke(): \stdClass
    {
        return $this->handle(new \stdClass());
    }
}

class IncorrectVariableReturn
{
    use HandleTrait;

    #[Route('/variable')]
    public function __invoke(): \stdClass
    {
        $result = $this->handle(new \stdClass());
        return $result;
    }
}
