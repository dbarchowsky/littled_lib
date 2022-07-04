<?php
namespace Littled\Request\Inline;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\IntegerInput;


class InlinePageInput extends InlineInput
{
	public IntegerInput $page;

	function __construct()
	{
		parent::__construct();
		$this->page = new IntegerInput("Page", "pn", true, null);
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
        $query = "SEL"."ECT `page_number` FROM `{$this->table->value}` WHERE id = ?";
		return array($query, 'i', &$this->parent_id->value);
	}

	/**
	 * @inheritDoc
	 */
	protected function formatUpdateQuery(): array
	{
        return $this->generateUpdateQuery();
	}

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        $query = "UPD"."ATE `{$this->table->value}` SET `page_number` = ? WHERE id = ?";
        return array($query, 'ii', &$this->page->value, &$this->parent_id->value);
    }

	/**
	 * Retrieves the access value and stores it in the object properties.
	 * @return void
	 * @throws InvalidQueryException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function read()
	{
		$data = parent::read();
		$this->page->value = $data[0]->page;
	}
}