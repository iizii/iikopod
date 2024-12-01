<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Exceptions;

final class FoodNotFoundException extends \DomainException
{
    public function __construct(string $message = 'Food not found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
