<?php
namespace Littled\Request\Inline;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\BooleanInput;


class InlineStatusInput extends InlineInput
{
	public BooleanInput $status;

	function __construct()
	{
		parent::__construct();
		$this->status = new BooleanInput("Status", "sid", true, null);
		$this->validateProperties[] = 'status';
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
        $query = "SEL"."ECT `enabled` FROM `{$this->table->value}` WHERE id = ?";
		return array($query, 'i' &$this->parent_id->value);
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
        $query = "UPD"."ATE `{$this->table->value}` SET `enabled` = ? WHERE id = ?";
        return array($query, 'ii', &$this->status->value, &$this->parent_id->value);
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
		$this->status->value = $data[0]->enabled;
	}
}