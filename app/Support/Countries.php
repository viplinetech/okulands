<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

/** Dialling codes for the enquiry form's phone field. Nigeria is first and is the default. */
class Countries
{
    public const DEFAULT = '+234';

    /** @return array<string, array{flag: string, name: string}> keyed by dialling code */
    public static function dialCodes(): array
    {
        return [
            '+234' => ['flag' => '🇳🇬', 'name' => 'Nigeria'],
            '+233' => ['flag' => '🇬🇭', 'name' => 'Ghana'],
            '+254' => ['flag' => '🇰🇪', 'name' => 'Kenya'],
            '+27' => ['flag' => '🇿🇦', 'name' => 'South Africa'],
            '+256' => ['flag' => '🇺🇬', 'name' => 'Uganda'],
            '+255' => ['flag' => '🇹🇿', 'name' => 'Tanzania'],
            '+237' => ['flag' => '🇨🇲', 'name' => 'Cameroon'],
            '+221' => ['flag' => '🇸🇳', 'name' => 'Senegal'],
            '+229' => ['flag' => '🇧🇯', 'name' => 'Benin'],
            '+228' => ['flag' => '🇹🇬', 'name' => 'Togo'],
            '+44' => ['flag' => '🇬🇧', 'name' => 'United Kingdom'],
            '+1' => ['flag' => '🇺🇸', 'name' => 'United States / Canada'],
            '+33' => ['flag' => '🇫🇷', 'name' => 'France'],
            '+49' => ['flag' => '🇩🇪', 'name' => 'Germany'],
            '+31' => ['flag' => '🇳🇱', 'name' => 'Netherlands'],
            '+971' => ['flag' => '🇦🇪', 'name' => 'United Arab Emirates'],
            '+966' => ['flag' => '🇸🇦', 'name' => 'Saudi Arabia'],
            '+86' => ['flag' => '🇨🇳', 'name' => 'China'],
            '+91' => ['flag' => '🇮🇳', 'name' => 'India'],
            '+61' => ['flag' => '🇦🇺', 'name' => 'Australia'],
        ];
    }

    public static function isValidCode(?string $code): bool
    {
        return $code !== null && array_key_exists($code, self::dialCodes());
    }

    /** Joins a dialling code and a local number into one international number, e.g. +2348012345678. */
    public static function combine(?string $code, string $local): string
    {
        $code = self::isValidCode($code) ? $code : self::DEFAULT;
        $digits = ltrim(preg_replace('/\D+/', '', $local), '0');

        return $code.$digits;
    }
}
