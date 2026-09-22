<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Validator\Doctrine;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class EntityExists
{
    public function __construct(
        public string $entityClass,
        public ?string $commandIdField = null,
        public array $groups = [],
    ) {}
}
