<?php
namespace Littled\Request;

/**
 * Classes using this trait are assumed to be derived from \Littled\PageContent\SerializedContent\SerializedContent.
 */
trait OptionsRetriever
{
    protected function formatOptionsQuery(): string
    {
        return 'SELECT `id`, `name` AS `label` FROM `' . static::getTableName() . '` ORDER BY `name`';
    }

    /**
     * Implemented in MySQLOperations
     */
    abstract public function fetchRecords(string $query, string $types = '', &...$vars): array;

    /**
     * Implemented in SerializedContentIO
     */
    abstract public static function getTableName(): string;

    /**
     * Retrieves a list of dropdown menu options from the database using the query defined in formatOptionsQuery().
     * @return DropdownOptions[]
     */
    public function retrieveOptions(): array
    {
        $result = $this->fetchRecords($this->formatOptionsQuery());
        $options = [];
        foreach ($result as $row) {
            $options[] = (new DropdownOptions())
                ->setValue($row->id)
                ->setLabel($row->label);
        }
        return $options;
    }
}