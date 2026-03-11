<?php declare(strict_types=1);

namespace Coala\MessengerBundle\Messenger;

trait HandleCommandBusTrait
{
    public function setCommandBus(object $bus): void {}
}

trait HandleQueryBusTrait
{
    public function setQueryBus(object $bus): void {}
}

trait HandleMessageBusTrait
{
    public function setMessageBus(object $bus): void {}
}
