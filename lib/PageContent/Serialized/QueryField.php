<?php

namespace Littled\PageContent\Serialized;


/**
 * Class containing values used to add parameters to a MySQLi prepared statement.
 */
class QueryField
{
    public bool $is_pk = false;
    public string $key;
    public string $type;
    /** @var mixed */
    public mixed $value;

    /**
     * Constructor. Initialized property values.
     * @param string $key
     * @param string $type
     * @param $value
     * @return void
     */
    public function __construct(string $key='', string $type='', $value = null)
    {
        $this->key = $key;
        $this->type = $type;
        $this->value = $value;
    }

    public function setisPrimaryKey(bool $flag): QueryField
    {
        $this->is_pk = $flag;
        return $this;
    }

    public function setKey(string $key): QueryField
    {
        $this->key = $key;
        return $this;
    }

    public function setType(string $type): QueryField
    {
        $this->type = $type;
        return $this;
    }

    public function setValue($value): QueryField
    {
        $this->value = $value;
        return $this;
    }
}