<?php

namespace LittledTests\DataProvider\Request;


class RequestInputTestData
{
    public string       $expected;
    public ?int         $index;
    public string       $key;
    public string       $label;
    public bool         $required;
    /** @var null|bool|int|string */
    public              $value;

    public const        DEFAULT_KEY = 'test_value';
    public const        DEFAULT_LABEL = 'Test';

    function __construct( string $expected, ?int $index=null, $value='', bool $required=false, string $key=self::DEFAULT_KEY, string $label=self::DEFAULT_LABEL)
    {
        $this->expected =   $expected;
        $this->index =      $index;
        $this->value =      $value;
        $this->required =   $required;
        $this->key   =      $key;
        $this->label =      $label;
    }
}