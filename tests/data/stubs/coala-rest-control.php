<?php /** @noinspection ALL */ declare(strict_types=1);

namespace Coala\RestControlBundle\Service\Serializer;

interface AppSerializer
{
    public function deserialize(string $data, string $type, string $format = 'json', $context = null): mixed;

    public function deserializeIntoExistingObject($data, object $existingObject, string $format = 'json', $context = null): object;
}
