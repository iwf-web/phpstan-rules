<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\Api\Security;

use Symfony\Component\Routing\Attribute\Route;

class LoginController
{
    #[Route('/login')]
    public function __invoke(): void
    {
    }
}
