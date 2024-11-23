<?php

declare(strict_types=1);

namespace Domain\Orders\Exceptions;

use Exception;

final class OrderStoreException extends Exception
{
    public function __construct(string $message = 'Order store error', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
