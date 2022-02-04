<?php

namespace Littled\Tests\Request\DataProvider;


class BooleanSelectTestDataProvider
{
    public static function renderInputTestProvider(): array
    {
        return array(
            [new BooleanSelectTestData('/^\s*<select '.
                'name=\"'.BooleanInputTestData::DEFAULT_KEY.'\" '.
                'id=\"'.BooleanInputTestData::DEFAULT_KEY.'\">\s*'.
                '<option value=\"1\">enabled<\/option>\s*'.
                '<option value=\"0\">disabled<\/option>\s*'.
                '<\/select>\s*/',
                '[use default]',
                array('1' => 'enabled', '0' => 'disabled'))],
        );
    }
}