<?php

declare(strict_types=1);

namespace Domain\Iiko\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;

final class UniquePrefixRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Проверка существования значения prefix в JSON-колонке price_categories
        $exists = DB::table('organization_settings')
            ->whereRaw("JSON_CONTAINS(price_categories, JSON_OBJECT('prefix', ?))", [$value])
            ->exists();

        if ($exists) {
            $fail('Поле Префикс должно быть уникальным.');
        }
    }
}
