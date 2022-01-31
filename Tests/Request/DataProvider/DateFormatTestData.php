<?php

namespace Littled\Tests\Request\DataProvider;

class DateFormatTestData
{
	/** @property string */
	public const DEFAULT_LABEL = 'Test Date';
	/** @property string */
	public const DEFAULT_KEY = 'p_date';

    /** @var string */
    public $date_string;
    /** @var string */
    public $format;
    /** @var string */
    public $expected;
    /** @var string */
    public $msg;

    /**
     * @param string|null $date_string
     * @param string|null $format
     * @param string|null $expected
     * @param string $msg
     */
    function __construct(?string $date_string='', ?string $format='', ?string $expected='', string $msg='')
    {
        $this->date_string = $date_string;
        $this->format = $format;
        $this->expected = $expected;
        $this->msg = $msg;
    }

    function dateFormatProvider(): array
    {
        return [$this->date_string, $this->format, $this->expected];
    }

    function dateStringProvider(): array
    {
        return [$this->date_string, $this->expected];
    }
}