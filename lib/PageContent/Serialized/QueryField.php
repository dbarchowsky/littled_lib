<?php

namespace Littled\PageContent\Serialized;


/**
 * Class containing values used to add parameters to a MySQLi prepared statement.
 */
class QueryField
{
    public string $key;
    public string $type;
    /** @var mixed */
    public $value;

    /**
     * Constructor. Initialized property values.
     * @param string $key
     * @param string $type
     * @param $value
     * @return void
     */
    public function __construct(string $key, string $type, $value)
    {
        $this->key = $key;
        $this->type = $type;
        $this->value = $value;
    }
}