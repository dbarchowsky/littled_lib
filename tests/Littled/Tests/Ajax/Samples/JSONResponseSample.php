<?php

namespace Littled\Tests\Ajax\Samples;

use Littled\Ajax\JSONField;
use Littled\Ajax\JSONResponse;

class JSONResponseSample extends JSONResponse
{
    /** @var JSONField */
    public $f1;
    /** @var JSONField */
    public $f2;

    function __construct(string $key = '')
    {
        parent::__construct($key);
        $this->f1 = new JSONField('field_1', 'test value one');
        $this->f2 = new JSONField('field_2', 'test value two');
    }
}