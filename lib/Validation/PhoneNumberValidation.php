<?php

namespace Littled\Validation;


use Littled\Exception\InvalidValueException;

class PhoneNumberValidation extends InputValidation
{
    private const US_PHONE_REGEX = [
        // Pattern for numbers with matching parentheses
        '/^\s*(?:\+?1[-.\s]*)?\(([2-9][0-9]{2})\)[-.\s]*([2-9][0-9]{2})[-.\s]*([0-9]{4})\s*$/',
        // Pattern for numbers without any parentheses
        '/^\s*(?:\+?1[-.\s]*)?((?<![\(\)])[2-9][0-9]{2}(?![\(\)]))[-.\s]*([2-9][0-9]{2})[-.\s]*([0-9]{4})\s*$/'
    ];
    private const E164_PHONE_REGEX = ')([0-9]+)$/';
    private static array $COUNTRY_CODES = [
        '1' => [10, 10],     // USA/Canada (NANP)
        '20' => [9, 9],      // Egypt
        '27' => [9, 9],      // South Africa
        '30' => [10, 10],    // Greece
        '31' => [9, 9],      // Netherlands
        '32' => [9, 9],      // Belgium
        '33' => [9, 9],      // France
        '34' => [9, 9],      // Spain
        '36' => [9, 9],      // Hungary
        '39' => [10, 10],    // Italy
        '40' => [9, 9],      // Romania
        '41' => [9, 9],      // Switzerland
        '43' => [10, 11],    // Austria
        '44' => [10, 10],    // UK
        '45' => [8, 8],      // Denmark
        '46' => [9, 9],      // Sweden
        '47' => [8, 8],      // Norway
        '48' => [9, 9],      // Poland
        '49' => [10, 11],    // Germany
        '51' => [9, 9],      // Peru
        '52' => [10, 10],    // Mexico
        '54' => [10, 10],    // Argentina
        '55' => [10, 11],    // Brazil
        '56' => [9, 9],      // Chile
        '57' => [10, 10],    // Colombia
        '58' => [10, 10],    // Venezuela
        '60' => [9, 10],     // Malaysia
        '61' => [9, 9],      // Australia
        '62' => [10, 12],    // Indonesia
        '63' => [10, 10],    // Philippines
        '64' => [9, 9],      // New Zealand
        '65' => [8, 8],      // Singapore
        '66' => [9, 9],      // Thailand
        '81' => [9, 10],    // Japan
        '82' => [9, 10],     // South Korea
        '84' => [9, 10],     // Vietnam
        '86' => [11, 11],    // China
        '90' => [10, 10],    // Turkey
        '91' => [10, 10],    // India
        '92' => [10, 10],    // Pakistan
        '93' => [9, 9],      // Afghanistan
        '94' => [9, 9],      // Sri Lanka
        '95' => [9, 10],     // Myanmar
        '98' => [10, 10],    // Iran
        '212' => [9, 9],     // Morocco
        '213' => [9, 9],     // Algeria
        '216' => [8, 8],     // Tunisia
        '218' => [9, 9],     // Libya
        '220' => [7, 7],     // Gambia
        '221' => [9, 9],     // Senegal
        '351' => [9, 9],     // Portugal
        '352' => [9, 9],     // Luxembourg
        '353' => [9, 9],     // Ireland
        '354' => [7, 9],     // Iceland
        '355' => [9, 9],     // Albania
        '359' => [9, 9],     // Bulgaria
        '380' => [9, 9],     // Ukraine
        '420' => [9, 9],     // Czech Republic
        '421' => [9, 9],     // Slovakia
        '972' => [9, 9],     // Israel
        '977' => [10, 10],   // Nepal
    ];

    /**
     * Tests if a phone number is a valid international phone number.
     * @param string $value
     * @return void
     * @throws InvalidValueException
     */
    public static function isInternationalPhoneNumber(string $value): void
    {
        if (str_contains($value, '++')) {
            throw new InvalidValueException('Invalid characters.');
        }

        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $value);

        if (empty($cleanPhone)) {
            throw new InvalidValueException('Phone number is empty.');
        }

        // Basic pattern for international numbers
        // Captures the country code and the rest of the number
        $pattern = '/^(?:\+?)(';

        // Build pattern with all country codes
        $countryCodesPattern = implode('|', array_keys(self::$COUNTRY_CODES));
        $pattern .= $countryCodesPattern;

        // Complete the pattern
        $pattern .= self::E164_PHONE_REGEX;

        if (!preg_match($pattern, $cleanPhone, $matches)) {
            throw new InvalidValueException('Invalid country code or format.');
        }

        $countryCode = $matches[1];
        $nationalNumber = $matches[2];

        // Get expected length range for this country code
        if (!isset(self::$COUNTRY_CODES[$countryCode])) {
            throw new InvalidValueException('Unsupported country code.');
        }

        [$minLength, $maxLength] = self::$COUNTRY_CODES[$countryCode];
        $numberLength = strlen($nationalNumber);

        if ($numberLength < $minLength || $numberLength > $maxLength) {
            throw new InvalidValueException("Invalid length for country code +$countryCode. " .
                    "Expected $minLength-$maxLength digits, got $numberLength");
        }
    }

    /**
     * Tests if a phone number is valid inside and outside the U.S.
     * @param string $value
     * @return void
     * @throws InvalidValueException
     */
    public static function isPhoneNumber(string $value): void
    {
        try {
            self::isUSPhoneNumber($value);
            return;
        } catch (InvalidValueException) {
            /* continue */
        }
        self::isInternationalPhoneNumber($value);
    }

    /**
     * Tests if a phone number is a valid phone number inside the U.S.
     * @param string $value
     * @return void
     * @throws InvalidValueException
     */
    public static function isUSPhoneNumber(string $value): void
    {
        foreach (self::US_PHONE_REGEX as $regex) {
            if (preg_match($regex, trim($value)) === 1) {
                return;
            }
        }
        throw new InvalidValueException('Invalid US phone number format.');
    }
}