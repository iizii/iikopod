<?php

declare(strict_types=1);

namespace Domain\Iiko\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;

final class IikoEventTypeNotFoundException extends \DomainException implements ShouldntReport {}
