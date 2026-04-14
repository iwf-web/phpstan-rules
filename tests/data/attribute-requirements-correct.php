<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\AttributeRequirementsCorrect;

use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes\Tag;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CompleteController
{
    #[Route('/complete')]
    #[Tag(name: 'foo')]
    #[IsGranted('ROLE_USER')]
    public function index(): void
    {
    }

    public function noRoute(): void
    {
    }
}
