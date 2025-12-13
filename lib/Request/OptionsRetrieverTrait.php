<?php
namespace Littled\Request;

/**
 * Classes using this trait are assumed to be derived from \Littled\PageContent\SerializedContent\SerializedContent.
 *
 * The following methods are implemented in SerializedContentIO
 * @method getTableName(): string
 * @method static getTableName(): string
 */
trait OptionsRetrieverTrait
{
    /**
     * Returns the query used to retrieve dropdown menu options from the database.
     * The columns returned must include an "id" column and a "label" column.
     * @return array
     */
    protected function formatOptionsQuery(): array
    {
        return ['SELECT `id`, `name` AS `label` FROM `' . $this->getTableName() . '` ORDER BY `name`'];
    }

    /**
     * Implemented in MySQLOperations
     */
    abstract public function fetchRecords(string $query, string $types = '', &...$vars): array;

    /**
     * Implemented in SerializedContentIO
     */
    abstract protected function _getTableName(): string;

    /**
     * Retrieves a list of dropdown menu options from the database using the query defined in formatOptionsQuery().
     * @return DropdownOptions[]
     */
    public function retrieveOptions(): array
    {
        $result = $this->fetchRecords(...$this->formatOptionsQuery());
        $options = [];
        foreach ($result as $row) {
            $options[] = (new DropdownOptions())
                ->setValue($row->id)
                ->setLabel($row->label);
        }
        return $options;
    }
}