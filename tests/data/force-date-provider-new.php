<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\DateProvider;

class DateExamples
{
    public function noArgs(): void
    {
        $a = new \DateTime(); // @error iwfWeb.forceDateProviderNew
        $b = new \DateTimeImmutable(); // @error iwfWeb.forceDateProviderNew
    }

    public function relativeString(): void
    {
        $a = new \DateTime('now'); // @error iwfWeb.forceDateProviderNew
        $b = new \DateTimeImmutable('tomorrow'); // @error iwfWeb.forceDateProviderNew
        $c = new \DateTime('+1 day'); // @error iwfWeb.forceDateProviderNew
    }

    public function absoluteString(): void
    {
        $a = new \DateTime('2025-01-01');
        $b = new \DateTimeImmutable('2025-06-15 12:00:00');
    }

    public function variableArg(\DateTimeInterface $dt): void
    {
        $fmt = $dt->format('Y-m-d');
        $a = new \DateTime($fmt);
    }

    public function edgeCases(): void
    {
        $class = 'DateTime';
        new $class(); // dynamic class — not a Node\Name, no error
        new \stdClass(); // not in target classes, no error
        $args = ['2025-01-01'];
        new \DateTime(...$args); // spread — not a Node\Arg, no error
    }
}
