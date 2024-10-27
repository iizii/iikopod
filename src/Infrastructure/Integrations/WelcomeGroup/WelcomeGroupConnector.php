<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Shared\Infrastructure\Integrations\AbstractConnector;

final readonly class WelcomeGroupConnector extends AbstractConnector implements WelcomeGroupConnectorInterface {}
