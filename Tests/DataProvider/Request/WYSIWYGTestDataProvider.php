<?php

namespace Littled\Tests\DataProvider\Request;

use Littled\Request\WYSIWYGTextarea;

class WYSIWYGTestDataProvider
{
    public static function collectRequestDataTestProvider(): array
    {
        return array(
            array('/^$/', '', '', [], 'no data'),
            array('/^this is the original source$/', 'p1', 'this is the original source', [], '', 'nothing to strip'),
            array('/^<p>this is the original source<\/p>/', 'p1', '<p>this is the original source</p><div>div content</div>', [], '', 'p tag whitelisted'),
            array('/<div>div content<\/div>$/', 'p1', '<p>this is the original source</p><div>div content</div>', [], '', 'div tag whitelisted'),
            array('/^<p>this is the original source<\/p><div>div content<\/div>$/', 'p1', '<p>this is the original source</p><div>div content</div>', [], 'POST', 'reading from POST data'),
            array('/^<p>this is the original source<\/p><div>div content<\/div>$/', 'p1', '<p>this is the original source</p><div>div content</div>', [], 'REQUEST', 'reading from REQUEST data'),
            array('/^<p>this is the original source<\/p><img.*\/><div>div content<\/div>$/', 'p1', '<p>this is the original source</p><img src="/assets/images/image.jpg" alt="test" /><div>div content</div>', [], '', 'img tag whitelisted'),
            array('/^<p>this is the original source<\/p><img.*\/>alert.*\)<div>div content<\/div>$/', 'p1', '<p>this is the original source</p><img src="/assets/images/image.jpg" alt="test" /><script>alert("hello there!")</script><div>div content</div>', [], '', 'strip script tag'),
        );
    }

    public static function renderInputTestProvider(): array
    {
        return array(
            array('/<textarea.*class=\"'.WYSIWYGTextarea::getEditorClass().'\"/', '', 'default editor class'),
            array('/<textarea.*class=\"my-other-class '.WYSIWYGTextarea::getEditorClass().'\"/', 'my-other-class', 'extra input class'),
            array('/<textarea.*class=\"'.WYSIWYGTextarea::getEditorClass().'\"/', WYSIWYGTextarea::getEditorClass(), 're-assign default editor class'),
        );
    }

    public static function setInputCSSClassTestProvider(): array
    {
        return array(
            array(WYSIWYGTextarea::getEditorClass(), '', 'default editor class'),
            array('my-other-class '.WYSIWYGTextarea::getEditorClass(), 'my-other-class', 'extra input class'),
            array(WYSIWYGTextarea::getEditorClass(), WYSIWYGTextarea::getEditorClass(), 're-assign default editor class'),
        );
    }
}