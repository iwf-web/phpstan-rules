<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App;

use OpenApi\Attributes as WrongAlias; // @error iwfWeb.requiredUseAlias
use Symfony\Component\Validator\Constraints as Validation; // @error iwfWeb.requiredUseAlias
use Coala\ModelMappingBundle\Annotation; // @error iwfWeb.requiredUseAlias
use Symfony\Component\Serializer\Attribute as Serializer;
use SomeOther\Library\Foo;
use function array_map; // use function — not TYPE_NORMAL, no error
use const PHP_EOL; // use const — not TYPE_NORMAL, no error
