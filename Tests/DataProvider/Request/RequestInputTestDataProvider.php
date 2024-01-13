<?php

namespace LittledTests\DataProvider\Request;

use Littled\Request\RequestInput;

class RequestInputTestDataProvider
{
    /** @var string */
    public const TEST_LABEL = 'My Request Input';
    /** @var string */
    public const TEST_KEY = 'requestKey';

    public static function escapeSQLTestProvider(): array
    {
        return array(
            array(null, '[use default]'),
            array(null, '[use default]', true),
            array(null, null),
            array(null, null, true),
            array("''", '', true),
            array('', ''),
            array('foobar', 'foobar'),
            array("'foobar'", 'foobar', true),
            array("single's quote", "single's quote"),
            array("multiple\nlines", "multiple\nlines"),
            array("'foobar'", 'foobar', true),
            array('122', 122),
            array('67.23', 67.23),
            array('0', false),
            array('1', true),
            array('0', 0),
            array('1', 1),
        );
    }

    public static function formatAttributeMarkupTestProvider(): array
    {
        return array(
            array('', []),
            array(' data-tid="3"', array('data-tid' => 3)),
            array(' foo="bar" biz="bash"', array('foo' => 'bar', 'biz' => 'bash')),
        );
    }

    public static function formatClassAttributeTestProvider(): array
    {
        return array(
            [''],
            [' class="my-class"', 'my-class'],
            [' class="my-class custom-class"', 'my-class', 'custom-class'],
            [' class="my-class input-error"', 'my-class', '', true],
            [' class="input-error"', '', '', true],
            [' class="my-class custom-class input-error"', 'my-class', 'custom-class', true],
            [' class="my-class"', 'my-class', '', false, 'input'],
            [' class="my-container-class"', 'my-container-class', '', false, 'container'],
        );
    }

    public static function isEmptyTestProvider(): array
    {
        return array(
            array(true, null),
            array(true, ''),
            array(true, ' '),
            array(false, 1),
            array(false, 0),
            array(false, 16),
            array(false, 16.23),
            array(false, -8),
            array(false, false),
            array(false, true),
        );
    }

    public static function saveInFormTestProvider(): array
    {
        return array(
            [new RequestInputTestData(
                '/<'.'input type="hidden" name="'.RequestInputTestData::DEFAULT_KEY.'" id="'.RequestInputTestData::DEFAULT_KEY.'" value="" \/>/'
            )],
            [new RequestInputTestData(
                '/<'.'input type="hidden" name="'.RequestInputTestData::DEFAULT_KEY.'\[0\]" id="'.RequestInputTestData::DEFAULT_KEY.'\[0\]" value="" \/>/',
                0
            )],
            [new RequestInputTestData(
                '/<'.'input type="hidden" name="'.RequestInputTestData::DEFAULT_KEY.'\[4\]" id="'.RequestInputTestData::DEFAULT_KEY.'\[4\]" value="" \/>/',
                4
            )],
        );
    }
}