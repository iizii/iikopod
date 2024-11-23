<?php

declare(strict_types=1);

namespace Domain\Orders\Exceptions;

use Exception;

final class OrderNotFoundException extends Exception
{
    public function __construct(string $message = 'Order not found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
