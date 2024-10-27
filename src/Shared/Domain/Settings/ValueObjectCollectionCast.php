<?php

declare(strict_types=1);

namespace Shared\Domain\Settings;

use Shared\Domain\ValueObject;
use Shared\Domain\ValueObjects\ValueObjectCollection;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

final readonly class ValueObjectCollectionCast implements SettingsCast
{
    public function __construct(
        private string $collectionClass,
        private string $valueObjectClass,
    ) {}

    /**
     * @return ValueObjectCollection<int, ValueObject>
     */
    public function get(mixed $payload): ValueObjectCollection
    {
        if (! is_array($payload)) {
            throw new \InvalidArgumentException('Payload should be an array');
        }

        /** @var ValueObjectCollection<int, ValueObject> $collection */
        $collection = new $this->collectionClass();

        /** @var ValueObject $valueObjectClass */
        $valueObjectClass = $this->valueObjectClass;

        foreach ($payload as $data) {
            $valueObjectInstance = $valueObjectClass::from($data);
            $collection->add($valueObjectInstance);
        }

        return $collection;
    }

    public function set(mixed $payload): mixed
    {
        return $payload;
    }
}
