<?php

namespace Littled\Tests\Request\DataProvider;

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
}