<?php

declare(strict_types=1);

namespace Domain\Settings\Exceptions;

use Exception;

final class OrganizationNotFoundException extends Exception
{
    public function __construct(string $message = 'Organization not found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
