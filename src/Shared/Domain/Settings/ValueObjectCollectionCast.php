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

    public function get($payload): ValueObjectCollection
    {
        if (! is_array($payload)) {
            throw new \InvalidArgumentException('Payload should be an array');
        }

        /** @var ValueObjectCollection $collection */
        $collection = new $this->collectionClass();

        /** @var ValueObject $valueObjectClass */
        $valueObjectClass = $this->valueObjectClass;

        foreach ($payload as $data) {
            $valueObjectClass = $valueObjectClass::from($data);
            $collection->add($valueObjectClass);
        }

        return $collection;
    }

    public function set($payload): mixed
    {
        return $payload;
    }
}
