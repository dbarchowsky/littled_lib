<?php
namespace Littled\Request\Inline;

use Exception;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;


/**
 * Class InlineInput
 * @package Littled\Request\Inline
 */
abstract class InlineInput extends SerializedContent
{
    /** @var IntegerInput Parent record id. */
    public IntegerInput $parent_id;
    /** @var StringInput Name of table in database that stores the value that is being updated. */
    public StringInput $table;
    /** @var StringInput Operation to be performed, e.g. "edit", "delete", etc. */
    public StringInput $op;
    /** @var array Property values to validate after changes are made in an HTML form. */
    public array $validateProperties;
    /** @var array Array of validation errors. */
    public array $validationErrors = [];
    /** @var string[] Possible column names. */
    public array $columnNameOptions = [];
    /** @var string Name of column holding date value. */
    public string $column_name = '';

    /**
     * @inheritdoc
     */
    function __construct(int|null $id = null)
    {
        parent::__construct($id);
        $this->parent_id = new IntegerInput('Parent id', 'id', true, false);
        $this->table = new StringInput('Table', 't', true, '', 200);
        $this->op = new StringInput('Operation', 'op', true, '', 20);
        $this->validateProperties = ['parent_id', 'table'];
    }

    /**
     * @param string $column_name
     * @param string $table_name
     * @return bool
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function columnExists(string $column_name, string $table_name = ''): bool
    {
        if ('' === $table_name) {
            $table_name = $this->table->value;
        }
        return parent::columnExists($column_name, $table_name);
    }

    /**
     * Placeholder for method that formats SQL query string to use to retrieve specific field values from the database.
     * @throws NotImplementedException
     */
    abstract protected function formatSelectQuery(): array;

    /**
     * Placeholder for method that formats SQL query string to use to update specific field values stored in the database.
     * @throws NotImplementedException
     */
    abstract protected function formatUpdateQuery(): array;

    /**
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    protected function getColumnName(): void
    {
        foreach ($this->columnNameOptions as $column) {
            if ($this->columnExists($column, $this->table->value)) {
                $this->column_name = $column;
                return;
            }
        }
        throw new RecordNotFoundException('No matching columns were found.');
    }

    /**
     * Retrieves data from database used to fill inline HTML forms.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function read(): InlineInput
    {
        if (count($this->columnNameOptions) > 0) {
            $this->getColumnName();
        }
        parent::read();
        return $this;
    }

    /**
     * Commits changes made to specific field values through inline HTML forms.
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function save(): void
    {
        if (count($this->columnNameOptions) > 0) {
            $this->getColumnName();
        }
        call_user_func_array([$this, 'query'], $this->formatUpdateQuery());
    }

    /**
     * Validates inline HTML edit values.
     * @throws ContentValidationException
     */
    public function validateInlineInput(): void
    {
        foreach ($this->validateProperties as $key) {
            try {
                /** @var RequestInput $property */
                $property = $this->$key;
                $property->validate();
            } catch (ContentValidationException $ex) {
                $this->validationErrors[] = $ex->getMessage();
            }
        }
        if (count($this->validationErrors) > 0) {
            throw new ContentValidationException('There were problems found in the information that was entered.');
        }
    }
}