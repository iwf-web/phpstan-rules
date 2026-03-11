<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\AttributeRequirements;

use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes\Tag;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SomeController
{
    #[Route('/missing-all')] // @error iwf.attributeRequirements @error iwf.attributeRequirements
    public function index(): void
    {
    }

    #[Route('/missing-isgrant')] // @error iwf.attributeRequirements
    #[Tag(name: 'bar')]
    public function show(): void
    {
    }

    #[Route('/complete')]
    #[Tag(name: 'baz')]
    #[IsGranted('ROLE_USER')]
    public function complete(): void
    {
    }

    public function noRoute(): void
    {
    }
}
