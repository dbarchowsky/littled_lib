<?php
namespace LittledTests\DataProvider\Validation;

class CheckSourceValueTestExpectations
{
    public bool     $return_value;
    public string   $key;
    /** @var mixed */
    public          $value;

    public function __construct(bool $return_value, string $key, $value=null)
    {
        $this->key = $key;
        $this->return_value = $return_value;
        $this->value = $value;
    }
}