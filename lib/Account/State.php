<?php

namespace Littled\Account;


use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanSelect;
use Littled\Request\FloatTextField;
use Littled\Request\StringTextField;

class State extends SerializedContent
{
    public StringTextField $name;
    public StringTextField $abbrev;
    public FloatTextField $sales_tax;
    public BooleanSelect $charge_tax;

    protected static string $table_name = 'states';

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->id->setKey('stateId');
        $this->name = new StringTextField('Name', 'stateName', true, '', 50);
        $this->abbrev = new StringTextField('Abbreviation', 'stateAbbrev', true, '', 2);
        $this->sales_tax = new FloatTextField('Sales tax', 'stateTax');
        $this->charge_tax = new BooleanSelect('Charge tax', 'stateCharge', false, false);
    }

    /**
     * @inheritDoc
     */
    public function hasRecordData(): bool
    {
        return $this->name->hasData() || $this->abbrev->hasData();
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        return 'state';
    }
}