<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\IsGranted;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MethodLevelController
{
    #[Route('/method')]
    #[IsGranted('ROLE_USER')]
    public function __invoke(): void
    {
    }
}

#[IsGranted('ROLE_USER')]
class ClassLevelController
{
    #[Route('/class')]
    public function __invoke(): void
    {
    }
}

class MissingIsGrantedController
{
    #[Route('/missing')] // @error iwfWeb.controllerMissingIsGranted
    public function __invoke(): void
    {
    }
}

class MultiMethodController
{
    #[Route('/first')]
    #[IsGranted('ROLE_USER')]
    public function first(): void
    {
    }

    #[Route('/second')] // @error iwfWeb.controllerMissingIsGranted
    public function second(): void
    {
    }

    private function helper(): void
    {
    }
}
