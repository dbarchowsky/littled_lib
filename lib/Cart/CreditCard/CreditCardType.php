<?php

namespace Littled\Cart\CreditCard;

use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\StringTextField;


class CreditCardType extends SerializedContent
{
    public StringTextField $name;
    public BooleanCheckbox $enabled;

    protected static string $table_name = 'card_type';

    public const int VISA_ID = 1;
    public const int MASTERCARD_ID = 2;
    public const int AMEX_ID = 3;
    public const int DISCOVER_ID = 4;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->name = (new StringTextField())
            ->setLabel('Name')
            ->setKey('ctName')
            ->setAsRequired()
            ->setSizeLimit(50);
        $this->enabled = (new BooleanCheckbox())
            ->setLabel('Enabled')
            ->setKey('ctEnabled')
            ->setAsNotRequired();
    }

    protected function hasRecordData(): bool
    {
        return $this->name->hasData();
    }

    public function getContentLabel(): string
    {
        return 'card type';
    }
}