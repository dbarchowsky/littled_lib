<?php

namespace LittledTests\DataProvider\Log;

class LogTestDataProvider
{
    public static function displayExceptionMessageTestProvider(): array
    {
        return array(
            array(
                '/<div class=\"alert alert-error\"><pre>.*division by zero(.|\n)*LogTest.*testDisplayExceptionMessage(.|\n)*<\/pre><\/div>/i',
                false, true
            ),
            array('/<pre>.*the exception message(.|\n)*<\/pre>/',
                true, true),
            array('/<div class=\"alert alert-error\">.*my custom message.*<\/div>/',
                true, false, 'This is my custom message, not the exception message.'),
            array('/<div class=\"alert alert-error\">.*my custom message.*<\/div>/',
                false, false, 'This is my custom message, not the exception message.'),
            array('/<div class=\"alert alert-error\">.*my custom message.*<\/div>/',
                true, false, 'This is my custom message, not the exception message.'),
        );
    }
}