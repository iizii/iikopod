<?php

declare(strict_types=1);

namespace Shared\Domain\Exceptions;

use Exception;
final class WelcomeGroupNotFoundMatchForPaymentTypeException extends Exception implements ShouldNotifyOperator {}
