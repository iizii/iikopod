<?php

declare(strict_types=1);

namespace Shared\Domain\Exceptions;

use Exception;

final class WelcomeGroupImportOrdersGeneralException extends Exception implements ShouldNotifyOperator {}
