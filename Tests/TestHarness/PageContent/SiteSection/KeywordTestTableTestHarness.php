<?php
namespace LittledTests\TestHarness\PageContent\SiteSection;

use Littled\PageContent\SiteSection\KeywordSectionContent;
use Littled\Request\BooleanSelect;
use Littled\Request\DateTextField;
use Littled\Request\IntegerTextField;
use Littled\Request\StringTextField;

class KeywordTestTableTestHarness extends KeywordSectionContent
{
    public StringTextField      $name;
    public IntegerTextField     $int_col;
    public BooleanSelect        $bool_col;
    public DateTextField        $date;
    public IntegerTextField     $slot;

    public const                CONTENT_TYPE_ID = 6037; /* << test_table table in littledamien database */
    public const                EXISTING_PARENT_ID = 28892;
    protected static int        $content_type_id = self::CONTENT_TYPE_ID;
    protected static string     $table_name = 'test_table';

    public function __construct($id = null, $content_type_id = null)
    {
        parent::__construct($id, $content_type_id);
        $this->name = new StringTextField('Name', 'kwName', true, '', 50);
        $this->int_col = new IntegerTextField('Integer value', 'kwInt');
        $this->bool_col = new BooleanSelect('Boolean value', 'kwBool');
        $this->date = new DateTextField('Date', 'kwDate');
        $this->slot = new IntegerTextField('Slot', 'kwSlot');
    }
}