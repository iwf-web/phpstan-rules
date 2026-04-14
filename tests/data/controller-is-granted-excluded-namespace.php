<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\Api\Public;

use Symfony\Component\Routing\Attribute\Route;

class PublicApiController
{
    #[Route('/public/resource')]
    public function __invoke(): void
    {
    }
}
