<?php

namespace LittledTests\TestHarness\PageContent\Serialized;

use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

class KeywordTestHarness extends SerializedContent
{
    /** @var string */
    protected static string $table_name='keyword';
    /** @var StringInput */
    public StringInput $term;
    /** @var IntegerInput */
    public IntegerInput $parent_id;
    /** @var IntegerInput */
    public IntegerInput $type_id;

    /**
     * Class constructor.
     * @param int|null $id
     * @param string $term
     * @param int|null $parent_id
     * @param int|null $type_id
     */
    function __construct(?int $id = null, string $term='', ?int $parent_id=null, ?int $type_id=null)
    {
        parent::__construct($id);
        $this->term = new StringInput('term', 'kwTerm', false, $term, 50);
        $this->parent_id = new IntegerInput('parent id', 'kwPid', false, $parent_id);
        $this->type_id = new IntegerInput('type id', 'kwTid', false, $type_id);
    }

    public function generateUpdateQuery(): ?array
    {
        /**
         * Implement abstract method not referenced for unit test purposes.
         */
        return array();
    }

    function getContentLabel(): string
    {
        /* stub */
        return 'Keyword test harness';
    }
}