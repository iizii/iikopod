<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\ValueObjects\Menu\ItemGroupCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class MenuBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $organizationSettingId,
        private StringId $externalId,
        private int $revision,
        private string $name,
        private ?string $description,
        private ItemGroupCollection $itemGroups,
    ) {}

    public static function fromExisted(Menu $menu): self
    {
        return new self(
            $menu->id,
            $menu->organizationSettingId,
            $menu->externalId,
            $menu->revision,
            $menu->name,
            $menu->description,
            $menu->itemGroups,
        );
    }

    public function setId(IntegerId $id): MenuBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setOrganizationSettingId(IntegerId $organizationSettingId): MenuBuilder
    {
        $clone = clone $this;
        $clone->organizationSettingId = $organizationSettingId;

        return $clone;
    }

    public function setExternalId(StringId $externalId): MenuBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setRevision(int $revision): MenuBuilder
    {
        $clone = clone $this;
        $clone->revision = $revision;

        return $clone;
    }

    public function setName(string $name): MenuBuilder
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function setDescription(?string $description): MenuBuilder
    {
        $clone = clone $this;
        $clone->description = $description;

        return $clone;
    }

    public function setItemGroups(ItemGroupCollection $itemGroups): MenuBuilder
    {
        $clone = clone $this;
        $clone->itemGroups = $itemGroups;

        return $clone;
    }

    public function build(): Menu
    {
        return new Menu(
            $this->id,
            $this->organizationSettingId,
            $this->externalId,
            $this->revision,
            $this->name,
            $this->description,
            $this->itemGroups,
        );
    }
}
