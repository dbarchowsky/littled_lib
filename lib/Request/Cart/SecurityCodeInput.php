<?php

namespace Littled\Request\Cart;

use Littled\Cart\CreditCard\CreditCardType;
use Littled\Request\StringTextField;

class SecurityCodeInput extends StringTextField
{
    protected ?int $card_type_id;

    public function __construct(
        string $label = 'Security code',
        string $key = 'ccSc',
        bool $required = false,
        ?string $value = null,
        int $size_limit = 3,
        ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
    }

    /**
     * @return int|null
     */
    public function getCardType(): ?int
    {
        return $this->card_type_id ?? null;
    }

    /**
     * @param int|null $type_id
     * @return $this
     */
    public function setCardType(?int $type_id): static
    {
        $this->card_type_id = $type_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate(): void
    {
        $err_msg = 'Invalid ' . strtolower($this->label) . '.';
        if ($this->isRequired()) {
            if (empty($this->value)) {
                $this->throwValidationError(ucfirst(strtolower($this->label)) . ' is required.');
            }
            switch($this->getCardType()) {
                case CreditCardType::AMEX_ID:
                    if (strlen($this->value)<3 || strlen($this->value)>4) {
                        $this->throwValidationError($err_msg);
                    }
                    break;
                default:
                    if (strlen($this->value) != $this->size_limit) {
                        $this->throwValidationError($err_msg);
                    }
            }
        }
    }
}