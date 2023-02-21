<?php

namespace Littled\Tests\DataProvider\Request;

class DateFormatTestData
{
	/** @property string */
	public const DEFAULT_LABEL = 'Test Date';
	/** @property string */
	public const DEFAULT_KEY = 'p_date';

    public ?string $date_string;
    public string $format;
    public ?string $expected;
    public ?string $expected_exception_class;
    public ?string $expected_exception;
    public string $msg;

    /**
     * @param string|null $date_string
     * @param string|null $format
     * @param string|null $expected
     * @param string|null $expected_exception_class
     * @param string|null $expected_exception
     * @param string $msg
     */
    function __construct(
        ?string $date_string='',
        ?string $format='',
        ?string $expected='',
        ?string $expected_exception_class=null,
        ?string $expected_exception=null,
        string $msg='')
    {
        $this->date_string = $date_string;
        $this->format = $format;
        $this->expected = $expected;
        $this->expected_exception_class = $expected_exception_class;
        $this->expected_exception = $expected_exception;
        $this->msg = $msg;
    }

    function dateFormatProvider(): array
    {
        return [$this->date_string, $this->format, $this->expected];
    }

    function dateStringProvider(): array
    {
        return [$this->date_string, $this->expected, $this->expected_exception_class, $this->expected_exception];
    }

    function mapSetInputValueData(): array
    {
        return array($this->date_string, $this->expected);
    }
}