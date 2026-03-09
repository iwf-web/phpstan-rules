<?php /** @noinspection ALL */

declare(strict_types=1);

namespace App;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/legacy')]
#[IsGranted('ROLE_USER')]
class LegacyAnnotationController
{
}
