<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Builders;

use Domain\WelcomeGroup\Entities\RestaurantModifier;
use Shared\Domain\ValueObjects\IntegerId;

final class RestaurantModifierBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $welcomeGroupRestaurantId,
        private IntegerId $restaurantId,
        private IntegerId $modifierId,
        private IntegerId $welcomeGroupModifierId,
        private IntegerId $externalId,
        private string $status,
        private ?string $status_comment,

    ) {}

    public static function fromExisted(RestaurantModifier $restaurantFood): self
    {
        return new self(
            $restaurantFood->id,
            $restaurantFood->welcomeGroupRestaurantId,
            $restaurantFood->restaurantId,
            $restaurantFood->modifierId,
            $restaurantFood->welcomeGroupModifierId,
            $restaurantFood->externalId,
            $restaurantFood->status,
            $restaurantFood->statusComment,
        );
    }

    public function setId(IntegerId $id): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setExternalId(IntegerId $externalId): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setWelcomeGroupRestaurantId(IntegerId $welcomeGroupRestaurantId): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->welcomeGroupRestaurantId = $welcomeGroupRestaurantId;

        return $clone;
    }

    public function setWelcomeGroupModifierId(IntegerId $welcomeGroupModifierId): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->welcomeGroupModifierId = $welcomeGroupModifierId;

        return $clone;
    }

    public function setModifierId(IntegerId $modifierId): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->modifierId = $modifierId;

        return $clone;
    }

    public function setRestaurantId(IntegerId $restaurantId): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->restaurantId = $restaurantId;

        return $clone;
    }

    public function setStatusComment(string $statusComment): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->status_comment = $statusComment;

        return $clone;
    }

    public function setStatus(string $status): RestaurantModifierBuilder
    {
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function build(): RestaurantModifier
    {
        return new RestaurantModifier(
            $this->id,
            $this->restaurantId,
            $this->modifierId,
            $this->externalId,
            $this->welcomeGroupRestaurantId,
            $this->welcomeGroupModifierId,
            $this->status_comment,
            $this->status,
        );
    }
}
