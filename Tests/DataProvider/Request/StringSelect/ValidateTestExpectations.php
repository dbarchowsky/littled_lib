<?php
namespace LittledTests\DataProvider\Request\StringSelect;

class ValidateTestExpectations
{
    public string   $exception;
    public string   $exception_msg;
    public ?int      $count;

    public function __construct(
        string $exception,
        string $exception_msg='',
        ?int $count=null)
    {
        $this->count = $count;
        $this->exception = $exception;
        $this->exception_msg = $exception_msg;
    }
}