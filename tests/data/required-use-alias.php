<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App;

use OpenApi\Attributes as WrongAlias; // @error iwfWeb.requiredUseAlias
use Symfony\Component\Validator\Constraints as Validation; // @error iwfWeb.requiredUseAlias
use Coala\ModelMappingBundle\Annotation; // @error iwfWeb.requiredUseAlias
use Symfony\Component\Serializer\Attribute as Serializer;
use SomeOther\Library\Foo;
