<?php /** @noinspection ALL */ declare(strict_types=1);

namespace Symfony\Component\Security\Http\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class IsGranted {}
