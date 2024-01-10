<?php
namespace LittledTests\TestHarness\API;

use Littled\API\JSONField;
use Littled\API\JSONResponse;


class JSONResponseSample extends JSONResponse
{
    /** @var JSONField */
    public JSONField $f1;
    /** @var JSONField */
    public JSONField $f2;

    function __construct(string $key = '')
    {
        parent::__construct($key);
        $this->f1 = new JSONField('field_1', 'test value one');
        $this->f2 = new JSONField('field_2', 'test value two');
    }
}