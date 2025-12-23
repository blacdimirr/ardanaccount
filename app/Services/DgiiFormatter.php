<?php

namespace App\Services;

class DgiiFormatter
{
    public static function resolveIdType(?string $taxNumber): string
    {
        if (!$taxNumber) {
            return '';
        }

        $digits = preg_replace('/\D/', '', $taxNumber);

        // DGII: 1 = RNC, 2 = Cédula (heuristic by length)
        return strlen($digits) === 9 ? '1' : '2';
    }
}
