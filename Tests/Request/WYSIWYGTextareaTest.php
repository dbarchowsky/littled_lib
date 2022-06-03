<?php

namespace Littled\Tests\Request;

use Littled\Request\WYSIWYGTextarea;
use PHPUnit\Framework\TestCase;

class WYSIWYGTextareaTest extends TestCase
{
    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\WYSIWYGTestDataProvider::collectRequestDataTestProvider()
     * @param string $expected
     * @param string $key
     * @param string $src
     * @param array $whitelist_tags
     * @param string $collection
     * @param string $msg
     * @return void
     */
    function testCollectRequestData(string $expected, string $key, string $src, array $whitelist_tags, string $collection='', string $msg='')
    {
        $data = null;
        switch ($collection) {
            case 'POST':
                $_POST[$key] = $src;
                break;
            case 'REQUEST':
                $_REQUEST[$key] = $src;
                break;
            default:
                $data = array($key => $src);
        }
        $o = new WYSIWYGTextarea('WYSIWYG Test', 'p1');
        $o->collectRequestData($data);
        self::assertMatchesRegularExpression($expected, $o->value, $msg);
    }
}