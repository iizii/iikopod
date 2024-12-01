<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Exceptions;

use Exception;
use Shared\Domain\Exceptions\ShouldNotifyOperator;

final class IIkoIntegrationException extends Exception implements ShouldNotifyOperator {}
