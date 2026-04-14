<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\HandleBusTrait;

use Coala\MessengerBundle\Messenger\HandleCommandBusTrait;
use Coala\MessengerBundle\Messenger\HandleQueryBusTrait;

class MissingCommandBusTrait // @error iwfWeb.useHandleBusTrait
{
    public function setCommandBus(object $bus): void
    {
        // defined manually without the trait
    }
}

class MissingQueryBusTrait // @error iwfWeb.useHandleBusTrait
{
    public function setQueryBus(object $bus): void
    {
        // defined manually without the trait
    }
}

class WithCommandBusTrait
{
    use HandleCommandBusTrait;
}

class WithQueryBusTrait
{
    use HandleQueryBusTrait;
}

class NoSetter
{
    public function doSomething(): void {}
}

class HasBothSetterAndTrait
{
    use HandleCommandBusTrait;

    public function setCommandBus(object $bus): void {} // trait used + setter declared — no error
}
