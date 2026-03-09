<?php /** @noinspection ALL */ declare(strict_types=1);

namespace Some\Other\Namespace;

class SomeClass // no error: outside configured namespace
{
    public function setCommandBus(object $bus): void {}
}
