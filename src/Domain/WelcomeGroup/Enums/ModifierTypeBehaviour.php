<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Enums;

enum ModifierTypeBehaviour: string
{
    case SUBSET = 'subset';

    case ONE = 'one';

    public static function fromValue(int $value): ModifierTypeBehaviour
    {
        return $value > 1 ? self::SUBSET : self::ONE;
    }
}
