<?php /** @noinspection ALL */ declare(strict_types=1);

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
        return $this->handle(new \stdClass()); // @error iwfWeb.controllerHandleReturnType
    }
}

class IncorrectVariableReturn
{
    use HandleTrait;

    #[Route('/variable')]
    public function __invoke(): \stdClass
    {
        $result = $this->handle(new \stdClass());
        return $result; // @error iwfWeb.controllerHandleReturnType
    }
}

// Route + HandleTrait but handle() not in return expression — no error
class HandleCalledNotReturned
{
    use HandleTrait;

    #[Route('/no-handle-return')]
    public function __invoke(): object
    {
        $this->handle(new \stdClass());
        return new \stdClass();
    }
}

// Route + HandleTrait with bare return (no expression) — no error
class RouteWithBareReturn
{
    use HandleTrait;

    #[Route('/bare-return')]
    public function __invoke(): void
    {
        return;
    }
}

// Abstract method has null stmts — no error
abstract class AbstractHandleController
{
    use HandleTrait;

    #[Route('/abstract')]
    abstract public function action(): object;
}

// Trait containing HandleTrait — rule must recurse through nested traits
trait OuterHandleTrait
{
    use HandleTrait;
}

class ControllerWithNestedTrait
{
    use OuterHandleTrait;

    #[Route('/nested-trait')]
    public function __invoke(): \stdClass
    {
        return $this->handle(new \stdClass()); // @error iwfWeb.controllerHandleReturnType
    }
}

// HandleTrait on parent class — rule must walk up via getParentClass()
class BaseHandleController
{
    use HandleTrait;
}

class ChildHandleController extends BaseHandleController
{
    #[Route('/child')]
    public function __invoke(): \stdClass
    {
        return $this->handle(new \stdClass()); // @error iwfWeb.controllerHandleReturnType
    }
}

// Union containing object — acceptable; hits return true inside UnionType foreach (L234)
class CorrectObjectUnion
{
    use HandleTrait;

    #[Route('/object-union')]
    public function __invoke(): object|\stdClass
    {
        return $this->handle(new \stdClass());
    }
}

// Wrong union type (no object member) — error; covers UnionType branches
class IncorrectUnionReturn
{
    use HandleTrait;

    #[Route('/bad-union')]
    public function __invoke(): string|int
    {
        return $this->handle(new \stdClass()); // @error iwfWeb.controllerHandleReturnType
    }
}

// Wrong nullable type — error; covers NullableType branches
class IncorrectNullableReturn
{
    use HandleTrait;

    #[Route('/bad-nullable')]
    public function __invoke(): ?\stdClass
    {
        return $this->handle(new \stdClass()); // @error iwfWeb.controllerHandleReturnType
    }
}

// Wrong intersection (no object member) — error; covers IntersectionType branches
class IncorrectIntersectionReturn
{
    use HandleTrait;

    #[Route('/bad-intersection')]
    public function __invoke(): \Countable&\Stringable
    {
        return $this->handle(new \stdClass()); // @error iwfWeb.controllerHandleReturnType
    }
}
