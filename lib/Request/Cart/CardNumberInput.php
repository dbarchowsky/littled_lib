<?php

namespace Littled\Request\Cart;

use Littled\Cart\CreditCard\CreditCardType;
use Littled\Exception\ContentValidationException;
use Littled\Request\StringTextField;


class CardNumberInput extends StringTextField
{
    protected int|null $card_type_id;

    public function __construct(
        string $label = 'Card number',
        string $key = 'ccNo',
        bool $required = false,
        ?string $value = null,
        int $size_limit = 20,
        ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
    }

    /**
     * Returns card type ID.
     * @return int|null
     */
    public function getCardType(): ?int
    {
        return $this->card_type_id ?? null;
    }

    /**
     * Performs Luhn algorithm validation on the card number.
     * @param string $number
     * @return bool
     */
    protected static function isValidLuhn (string $number): bool
    {
        if (empty($number)) {
            return false;
        }
        $sum = 0;
        $len = strlen($number);
        $parity = $len % 2;
        for($i = strlen($number)-1; $i >= 0; $i--) {
            $digit = (int)$number[$i];
            if (($i % 2) === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        return ($sum % 10 === 0);
    }

    /**
     * Card type property value setter.
     * @param int|null $type_id
     * @return $this
     */
    public function setCardType(?int $type_id): static
    {
        $this->card_type_id = $type_id;
        return $this;
    }

    /**
     * Overrides parent method to validate card number.
     * @return void
     * @throws ContentValidationException
     */
    public function validate(): void
    {
        $number = $this->value;
        $this->value = preg_replace('/[^0-9]/', '', $this->value);
        parent::validate();
        $this->value = $number;

        if (!($this->card_type_id ?? 0) > 0) {
            $this->throwValidationError('A card type was not provided to validate the card number.');
        }

        $number = preg_replace('/[^0-9]/', '', $this->value);
        switch($this->card_type_id) {
            case CreditCardType::MASTERCARD_ID:
                if (strlen($number) !== 16) {
                    $this->throwValidationError('Invalid Mastercard number.');
                }
                if (!preg_match('/^(5[1-5]|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)/', $number)) {
                    $this->throwValidationError('Invalid Mastercard number.');
                }
                break;
            case CreditCardType::VISA_ID:
                if (!preg_match('/^4\d{12}(?:\d{3})?$/', $number)) {
                    $this->throwValidationError('Invalid Visa number.');
                }
                break;
            case CreditCardType::AMEX_ID:
                if (!preg_match('/^3[47][0-9]{13}$/', $number)) {
                    $this->throwValidationError('Invalid American Express number.');
                }
                break;
            case CreditCardType::DISCOVER_ID:
                if (!preg_match('/^6(?:011\d{2}|5\d{4}|4[4-9]\d{3}|22(?:1(?:2[6-9]|[3-9]\d)|[2-8]\d{2}|9(?:[01]\d|2[0-5])))\d{10}$/', $number)) {
                    $this->throwValidationError('Invalid Discover number.');
                }
                break;
            default:
                $this->throwValidationError('Unrecognized card type.');
        }

        // validate card number using mod 10 algorithm
        if (!static::isValidLuhn($number)) {
            $this->throwValidationError('Invalid card number.');
        }
    }
}