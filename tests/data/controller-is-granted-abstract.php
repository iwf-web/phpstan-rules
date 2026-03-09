<?php /** @noinspection ALL */

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

abstract class AbstractBaseController
{
    #[Route('/abstract')]
    public function __invoke(): void
    {
    }
}
