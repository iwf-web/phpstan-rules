<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\DateProvider;

class StaticCallExamples
{
    public function conversions(\DateTimeImmutable $immutable, \DateTime $mutable): void
    {
        $a = \DateTime::createFromImmutable($immutable); // allowed: converting existing object
        $b = \DateTimeImmutable::createFromMutable($mutable); // allowed: converting existing object
    }

    public function createFromFormatRelative(): void
    {
        $a = \DateTime::createFromFormat('Y-m-d', 'now'); // @error iwfWeb.forceDateProviderStaticCall
        $b = \DateTimeImmutable::createFromFormat('Y-m-d', 'tomorrow'); // @error iwfWeb.forceDateProviderStaticCall
        $c = \DateTime::createFromFormat('Y-m-d', '+1 day'); // @error iwfWeb.forceDateProviderStaticCall
    }

    public function createFromFormatAbsolute(): void
    {
        $a = \DateTime::createFromFormat('Y-m-d', '2025-01-01'); // allowed: absolute date
        $b = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2025-06-15 12:00:00'); // allowed
        $c = \DateTime::createFromFormat('U', '1234567890'); // allowed: unix timestamp
    }

    public function createFromFormatVariable(string $value): void
    {
        $a = \DateTime::createFromFormat('Y-m-d', $value); // allowed: cannot determine at analysis time
    }

    public function edgeCases(string $format): void
    {
        $class = 'DateTime';
        $class::createFromFormat('Y-m-d', 'now'); // dynamic class — not a Node\Name, no error
        $method = 'createFromFormat';
        \DateTime::$method('Y-m-d', 'now'); // dynamic method — not a Node\Identifier, no error
        \stdClass::createFromFormat('Y-m-d', 'now'); // not a target class, no error
        \DateTime::createFromFormat('Y-m-d'); // < 2 args, no error
        $args = ['Y-m-d', 'now'];
        \DateTime::createFromFormat(...$args); // spread — not a Node\Arg, no error
        \DateTime::createFromFormat($format, 'now'); // variable format — not a string literal, no error
    }
}
