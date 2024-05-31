<?php

namespace Littled\Database;

use Littled\Exception\InvalidQueryException;
use Exception;

/**
 * Class DBUtils
 * @package Littled\Database
 */
class DBUtils
{
    /**
     * Takes an array containing name/value pairs and writes them out as a series of HTML option tags.
     * @param array $options Array containing name/value pairs.
     * @param array $selected_options Array containing the values of any selected options.
     */
    public static function displayCachedOptions(array $options, array $selected_options): void
    {
        ?>
        <?php foreach ($options as $key => $val): ?>
        <option value="<?= $key ?>"<?= ((in_array($key, $selected_options)) ? (" selected=\"selected\"") : ("")) ?>><?= $val ?></option>
    <?php endforeach; ?>
        <?php
    }

    /**
     * Prints out all the possible values from an ENUM column in the database as a series of HTML option tags.
     * @param string $table_name Name of the table containing the ENUM column.
     * @param string $column Name of the ENUM column.
     * @param array $selected_options Array containing the values of any selected options.
     * @throws Exception
     */
    public static function displayEnumOptions(string $table_name, string $column, array $selected_options): void
    {
        try {
            $arOptions = DBUtils::getEnumOptions($table_name, $column);
            DBUtils::displayCachedOptions($arOptions, $selected_options);
        } catch (InvalidQueryException $ex) {
            ?>
            <option value="" disabled="disabled" style="background-color:#ff0000;color:#ffffff;font-weight:bold;">Error
            retrieving options: <?= $ex->getMessage() ?></option><?php
        }
    }

    /**
     * Uses supplied SQL SELECT statement to retrieve name/value pairs from database. These name/value pairs are then written out at HTML option tags.
     * @param string $query SQL SELECT statement
     * @param array $selected_options Array containing the values of any selected options.
     * @param string $css_error_class (Optional) css class to apply to option element in case of an error. Defaults to "alert alert-error".
     */
    public static function displayQueryOptions(string $query, array $selected_options, string $css_error_class = 'alert alert-error'): void
    {
        try {
            $conn = new MySQLConnection();
            $data = $conn->fetchRecords($query);
            if (count($data) > 0) {
                if (!property_exists($data[0], 'id') || !property_exists($data[0], 'name')) {
                    throw new InvalidQueryException("Missing required fields in query: \"id\" and/or \"name\".");
                }
            }
            foreach ($data as $row):
                ?>
                <option value="<?= $row->id ?>"<?= ((in_array($row->id, $selected_options)) ? (" selected=\"selected\"") : ("")) ?>><?= $row->name ?></option>
            <?php
            endforeach;
        } catch (Exception $ex) {
            ?>
            <option value="" disabled="disabled" class="<?= $css_error_class ?>">Error retrieving
                options: <?= $ex->getMessage() ?></option>
            <?php
        }
    }

    /**
     * Runs supplied SQL SELECT statement to retrieve recordset. Fills supplied array with the first value in each row of the recordset (all other values in the row are ignored).
     * @param string $query SQL SELECT query.
     * @param array $buffer Array where the results will be stored.
     * @throws InvalidQueryException|Exception
     */
    public static function fillArrayFromQuery(string $query, array &$buffer): void
    {
        $conn = new MySQLConnection();
        $data = $conn->fetchRecords($query);
        foreach ($data as $row) {
            /** TODO Referencing row elements by index might not work below. */
            $buffer[] = $row[0];
        }
    }

    /**
     * returns string containing values returned by database query formatted as a javascript array
     * @param string $query MySQL query to run to retrieve values
     * @return string database values formatted as a javascript array
     * @throws InvalidQueryException|Exception
     */
    public static function formatQueryJavascriptArray(string $query): string
    {
        $conn = new MySQLConnection();
        $tmp = array();
        $data = $conn->fetchRecords($query);
        foreach ($data as $row) {
            $tmp[] = "'" . preg_replace("/'/", "\\'", $row[0]) . "'";
        }
        return (implode(",", $tmp));
    }

    /**
     * Formats a date in a format that can be stored in a MySQl database.
     * @param int|null $timestamp Optional Time to be formatted in MySQL format. Time can be either an integer timestamp
     * or a string date value. If no date is provided, the current time will be returned.
     * @return string
     */
    public static function formatSqlDate(?int $timestamp = null): string
    {
        if ($timestamp && !is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        if ($timestamp === null) {
            $timestamp = time();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Retrieve all possible values for a given ENUM column in a table in the database.
     * @param string $table_name Name of table containing the ENUM column.
     * @param string $column Name of the ENUM column.
     * @return array Array containing all the possible values as name/value pairs.
     * @throws InvalidQueryException|Exception
     */
    public static function getEnumOptions(string $table_name, string $column): array
    {
        $conn = new MySQLConnection();
        $query = "SHOW COLUMNS FROM `$table_name` LIKE '$column'";
        $data = $conn->fetchRecords($query);
        if (count($data) < 1) {
            return (array());
        }
        $values = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/", "\\2", $data[0]->Type));
        $options = array();
        for ($i = 0; $i < count($values); $i++) {
            if (trim($values[$i]) != "") {
                $options[$values[$i]] = $values[$i];
            }
        }
        return ($options);
    }

    /**
     * Tests if a query string contains a procedure call.
     * @param string $query
     * @return bool
     */
    public static function isProcedure(string $query): bool
    {
        return (str_starts_with(strtolower(substr($query, 0, 5)), 'call '));
    }

    /**
     * Looks up the next sequential unused record id value in a table, assuming the primary key column is named 'id'.
     * @param string $table_name Name of the table to search.
     * @return int Value of the next sequential unused id.
     * @throws Exception
     */
    public static function lookupNextAvailableRecordId(string $table_name): int
    {
        $conn = new MySQLConnection();
        $query = 'SELECT MIN(t1.id + 1) AS next_id ' .
            'FROM ' . $table_name . ' AS t1 ' .
            'LEFT JOIN ' . $table_name . ' AS t2 ' .
            'ON t1.id + 1 = t2.id ' .
            'WHERE t2.id IS NULL';
        $data = $conn->fetchRecords($query);
        if (count($data) < 1) {
            throw new Exception('Temp id not available.');
        }
        return $data[0]->next_id;
    }

    /**
     * Fills $arOptions array with name/value pairs retrieved using the supplied SQL SELECT query.
     * @param string $query SQL SELECT query used to retrieve name/value array.
     * @param array $options Function will fill this array with name/value pairs to be used in option list.
     * @throws InvalidQueryException
     * @throws Exception
     */
    public static function retrieveOptionsList(string $query, array &$options): void
    {
        $conn = new MySQLConnection();

        $data = $conn->fetchRecords($query);
        foreach ($data as $row) {
            $options[$row[0]] = $row[1];
        }
    }
}