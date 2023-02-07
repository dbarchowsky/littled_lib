<?php

namespace Littled\Tests\TestHarness\PageContent\SiteSection;

use Littled\PageContent\SiteSection\SectionContent;
use Littled\Request\BooleanInput;
use Littled\Request\DateInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

/**
 * Implements abstract methods of SectionContent to allow objects in unit Tests.
 *
 */
class SectionContentTestHarness extends SectionContent
{
	public const CONTENT_TYPE_ID = 6037;
    protected static int $content_type_id = self::CONTENT_TYPE_ID;
	public static string $table_name = 'test_table';

	/* properties matching fields in the "TestTable" table */
	public StringInput $name;
	public IntegerInput $int_col;
	public BooleanInput $bool_col;
	public DateInput $date;
	public IntegerInput $slot;

	public function __construct(?int $id = null, ?int $content_type_id=null)
	{
		parent::__construct($id, $content_type_id);
		$this->name = new StringInput('Name', 'name', false, "", 50);
		$this->int_col = new IntegerInput('Integer column', 'intCol');
		$this->bool_col = new BooleanInput('Boolean column', 'boolCol');
		$this->date = new DateInput('Date', 'Date column');
		$this->slot = new IntegerInput('Slot', 'slot');
	}

    public function generateUpdateQuery(): ?array
    {
       return array();
    }
}