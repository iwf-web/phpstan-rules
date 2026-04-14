<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App;

use Symfony\Component\{
    Validator\Constraints as Validation, // @error iwfWeb.requiredUseAliasGroupUse
    Serializer\Attribute as Serializer,
};
use OpenApi\{Attributes as WrongAlias}; // @error iwfWeb.requiredUseAliasGroupUse
use function Symfony\Component\{String\u}; // function group use — not TYPE_NORMAL, no error
