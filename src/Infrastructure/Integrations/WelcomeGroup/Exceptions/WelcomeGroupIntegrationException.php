<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Exceptions;

use Exception;
use Shared\Domain\Exceptions\ShouldNotifyOperator;

final class WelcomeGroupIntegrationException extends Exception implements ShouldNotifyOperator {}
