<?php declare(strict_types=1);

namespace Coala\DateProviderBundle\Service\DateProvider;

interface DateProviderInterface
{
    public function now(): \DateTimeImmutable;
}
