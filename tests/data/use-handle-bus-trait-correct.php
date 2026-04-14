<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\HandleBusTraitCorrect;

use Coala\MessengerBundle\Messenger\HandleCommandBusTrait;
use Coala\MessengerBundle\Messenger\HandleQueryBusTrait;

class CorrectCommandBus
{
    use HandleCommandBusTrait;
}

class CorrectQueryBus
{
    use HandleQueryBusTrait;
}

class NoSetter
{
    public function doSomething(): void {}
}
