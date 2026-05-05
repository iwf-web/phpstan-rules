<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\Api\Combined;

use Symfony\Component\Routing\Attribute\Route;

class GloballyExcludedController
{
    #[Route('/globally-excluded')]
    public function __invoke(): void
    {
    }
}

class PerDefinitionExcludedController
{
    #[Route('/per-definition-excluded')]
    public function __invoke(): void
    {
    }
}

class TriggeringController
{
    #[Route('/triggering')] // @error iwfWeb.attributeRequirements @error iwfWeb.attributeRequirements
    public function __invoke(): void
    {
    }
}
