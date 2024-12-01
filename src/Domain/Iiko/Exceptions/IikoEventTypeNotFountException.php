<?php

declare(strict_types=1);

namespace Domain\Iiko\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;

final class IikoEventTypeNotFountException extends \DomainException implements ShouldntReport {}
