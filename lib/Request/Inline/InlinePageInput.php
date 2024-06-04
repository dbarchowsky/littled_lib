<?php
namespace Littled\Request\Inline;

use Littled\Request\IntegerInput;


abstract class InlinePageInput extends InlineInput
{
	public IntegerInput $page;

	function __construct()
	{
		parent::__construct();
		$this->page = new IntegerInput('Page', 'pn', true, null);
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
        $query = "SELECT `page_number` FROM `{$this->table->value}` WHERE id = ?";
		return [$query, 'i', &$this->parent_id->value];
	}

    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        $query = "UPDATE `{$this->table->value}` SET `page_number` = ? WHERE id = ?";
        return [$query, 'ii', &$this->page->value, &$this->parent_id->value];
    }

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->page->hasData();
    }

    /**
	 * @inheritDoc
	 */
	public function read(): InlinePageInput
    {
		$data = parent::read();
		$this->page->value = $data[0]->page;
        return $this;
	}
}