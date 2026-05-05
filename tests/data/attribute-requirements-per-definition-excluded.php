<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\Api\PerDefinition;

use Symfony\Component\Routing\Attribute\Route;

class ExcludedForRouteController
{
    #[Route('/excluded')]
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
