<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Builders;

use Domain\WelcomeGroup\Entities\RestaurantFood;
use Shared\Domain\ValueObjects\IntegerId;

final class RestaurantFoodBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $welcomeGroupRestaurantId,
        private IntegerId $restaurantId,
        private IntegerId $foodId,
        private IntegerId $welcomeGroupFoodId,
        private IntegerId $externalId,
        private string $status,
        private ?string $status_comment,

    ) {}

    public static function fromExisted(RestaurantFood $restaurantFood): self
    {
        return new self(
            $restaurantFood->id,
            $restaurantFood->welcomeGroupRestaurantId,
            $restaurantFood->restaurantId,
            $restaurantFood->foodId,
            $restaurantFood->welcomeGroupFoodId,
            $restaurantFood->externalId,
            $restaurantFood->status,
            $restaurantFood->statusComment,
        );
    }

    public function setId(IntegerId $id): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setExternalId(IntegerId $externalId): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setWelcomeGroupRestaurantId(IntegerId $welcomeGroupRestaurantId): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->welcomeGroupRestaurantId = $welcomeGroupRestaurantId;

        return $clone;
    }

    public function setWelcomeGroupFoodId(IntegerId $welcomeGroupFoodId): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->welcomeGroupFoodId = $welcomeGroupFoodId;

        return $clone;
    }

    public function setFoodId(IntegerId $foodId): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->foodId = $foodId;

        return $clone;
    }

    public function setRestaurantId(IntegerId $restaurantId): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->restaurantId = $restaurantId;

        return $clone;
    }

    public function setStatusComment(string $statusComment): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->status_comment = $statusComment;

        return $clone;
    }

    public function setStatus(string $status): RestaurantFoodBuilder
    {
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function build(): RestaurantFood
    {
        return new RestaurantFood(
            $this->id,
            $this->restaurantId,
            $this->foodId,
            $this->externalId,
            $this->welcomeGroupRestaurantId,
            $this->welcomeGroupFoodId,
            $this->status_comment,
            $this->status,
        );
    }
}
