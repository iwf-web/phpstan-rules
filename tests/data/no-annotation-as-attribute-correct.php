<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/correct')]
class CorrectAttributeController
{
}

#[\Attribute]
class MyCustomAttribute {} // non-Symfony prefix — no error
