<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotReservedSlug implements ValidationRule
{
    /**
     * Weiger slugs die botsen met top-level routes van het publieke deel.
     * Lijst staat in config/westein.php zodat 'ie op één plek beheerbaar is.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return; // andere regels (required/string) handelen leegte af
        }

        $reserved = config('westein.reserved_slugs', []);

        if (in_array(strtolower($value), $reserved, true)) {
            $fail('Deze slug is gereserveerd voor het systeem en kan niet als pagina-slug worden gebruikt.');
        }
    }
}
