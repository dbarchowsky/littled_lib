<?php

namespace LittledTests\DataProvider\PageContent;


class FormatDateTestData
{
    public string $date;
    public string $format;
    public string $expected;
    public string $msg;

    function __construct(string $date, string $format, string $expected, string $msg='')
    {
        $this->date = $date;
        $this->format = $format;
        $this->expected = $expected;
        $this->msg  = $msg;
    }
}