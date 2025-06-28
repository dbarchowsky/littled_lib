<?php
namespace Littled\Database;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\Validation\Validation;
use mysqli;
use mysqli_sql_exception;
use mysqli_result;


/**
 * MySQL I/O operations
 */
trait MySQLOperations
{
    /** @var mysqli Connection to a database server. */
    protected mysqli    $mysqli;

    /**
     * Closes mysqli connection.
     */
    public function closeDatabaseConnection(): void
    {
        if (isset($this->mysqli) && Validation::isSubclass($this->mysqli, mysqli::class)) {
            $this->mysqli->close();
        }
    }

    /**
     * Check if a column exists in a given database table.
     * @param string $column_name name of the column to check for
     * @param string $table_name name of the table to look in
     * @return bool TRUE if the column is found.
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function columnExists(string $column_name, string $table_name): bool
    {
        if (defined('MYSQL_SCHEMA')) {
            $schema = MYSQL_SCHEMA;
        } else {
            throw new ConfigurationUndefinedException('Schema undefined in ' . __METHOD__ . '.');
        }

        $query = 'SELECT EXISTS ' .
            '(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS ' .
            'WHERE TABLE_SCHEMA=? ' .
            'AND TABLE_NAME=? ' .
            'AND COLUMN_NAME=?) as `column_present`';

        $data = $this->fetchRecords($query, 'sss', $schema, $table_name, $column_name);
        if (count($data) > 0) {
            return $data[0]->column_present;
        }
        return false;
    }

    /**
     * Returns the latest connection error reported by mysqli.
     * @return string Internal mysqli connection error string, or null if there are no errors.
     */
    public function connectionError(): string
    {
        return ($this->mysqli->connect_error);
    }

    /**
     * Make database connection
     * @param DBConnectionSettings $c Database connection properties
     */
    protected function connect(DBConnectionSettings $c): void
    {
        if (preg_match('/^\d{1,3}\.\d{1,3}.\d{1,3}\.\d{1,3}$/', $c->host)) {
            $this->mysqli = new mysqli($c->host, $c->user, $c->password, $c->schema, $c->port);
        } else {
            if ($c->port) {
                $c->host .= ":$c->port";
            }
            $this->mysqli = new mysqli($c->host, $c->user, $c->password, $c->schema);
        }
    }

    /**
     * Opens MySQLi connection. Stores connection as $mysqli property of the class.
     * Can be chained with other MySQLConnection methods.
     * @param string $host Name of MySQL host.
     * @param string $user Username for connecting to MySQL server.
     * @param string $password Password for connecting to MySQL server.
     * @param string $schema Name of schema.
     * @param string $port Port number of MySQL server if not using default.
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public function connectToDatabase(
        string $host = '',
        string $user = '',
        string $password = '',
        string $schema = '',
        string $port = ''): void
    {
        if (!isset($this->mysqli)) {
            try {
                $this->connect(static::getConnectionSettings($host, $user, $password, $schema, $port));
            } catch (mysqli_sql_exception $ex) {
                throw new ConnectionException('Connection error: ' . $ex->__toString());
            }
            $this->mysqli->set_charset('utf8');
        }
    }

    /**
     * Escapes the object's value property for inclusion in SQL queries.
     * @param mixed $value Value to escape.
     * @return string|int|float Escaped value.
     * @throws ConfigurationUndefinedException
     */
    public function escapeSQLValue(mixed $value): float|int|string
    {
        if ($value === null) {
            return ('null');
        }
        if ($value === true) {
            return ('1');
        }
        if ($value === false) {
            return ('0');
        }
        if (is_numeric($value)) {
            return ($value);
        }
        return "'" . $this->getMySQLi()->real_escape_string($value) . "'";
    }

    /**
     * Returns associative array retrieved with a database query.
     * @param string $query SQL query to execute
     * @param string $types
     * @param mixed $vars,...
     * @return array Array of generic objects holding the data returned by the query.
     * @throws FailedQueryException
     */
    public function fetchOptions(string $query, string $types = '', &...$vars): array
    {
        if ($types) {
            array_unshift($vars, $query, $types);
            $result = $this->fetchResult(...$vars);
        } else {
            $result = $this->fetchResult($query);
        }
        $rs = array();
        while ($row = $result->fetch_object()) {
            if (count($rs) == 0 && (!property_exists($row, 'id') || !property_exists($row, 'option'))) {
                throw new FailedQueryException('Invalid query retrieving options.');
            }
            $rs[$row->id] = $row->option;
        }
        $result->free();
        return ($rs);
    }

    /**
     * Returns records from a database query. This routine will eat up all result sets returned by
     * the execution of the query. Use fetchRecordsNonExhaustive() to return only the first result.
     * @param string $query SQL query to execute
     * @param string $types
     * @param mixed $vars,...
     * @return array Array of generic objects holding the data returned by the query.
     * @throws FailedQueryException
     */
    public function fetchRecords(string $query, string $types = '', &...$vars): array
    {
        if ($types) {
            array_unshift($vars, $query, $types);
            $result = $this->fetchResult(...$vars);
        } else {
            $result = $this->fetchResult($query);
        }
        $rs = array();
        while ($row = $result->fetch_object()) {
            $rs[] = $row;
        }
        $result->free();
        return $rs;
    }

    /**
     * Returns mysqli_result object containing data matching query.
     * @param string $query
     * @param string $types
     * @param mixed $vars,...
     * @return mysqli_result
     * @throws FailedQueryException
     */
    public function fetchResult(string $query, string $types = '', &...$vars): mysqli_result
    {
        try {
            $this->connectToDatabase();
        }
        catch (ConfigurationUndefinedException|ConnectionException $ex) {
            $msg = 'Connection error. [' . Log::getClassBaseName($ex::class) . ']' . $ex->getMessage();
            throw new FailedQueryException($msg);
        }
        if ($types) {
            $stmt = $this->mysqli->prepare($query);
            if (!$stmt) {
                throw new FailedQueryException('Could not prepare statement: ' . $this->mysqli->error);
            }
            array_unshift($vars, $types);
            call_user_func_array([$stmt, 'bind_param'], $vars);
            if (!$stmt->execute()) {
                throw new FailedQueryException('Error fetching records: ' . $stmt->error);
            }
            $result = $stmt->get_result();
            $stmt->close();
        }
        else {
            try {
                $result = $this->mysqli->query($query);
            }
            catch (mysqli_sql_exception $ex) {
                throw new FailedQueryException('Error fetching records: ' . $ex->getMessage());
            }
            if (!$result) {
                throw new FailedQueryException('Error fetching records: ' . $this->mysqli->error);
            }

            /*
             * Eat up any extra record sets that might be generated by stored procedures
             */
            do {
                $b = $this->mysqli->store_result();
                if ($b) {
                    $b->free();
                }
            } while($this->mysqli->next_result());
        }
        return $result;
    }

    /**
     * @param string $query Query to execute.
     * @return array
     * @throws FailedQueryException
     * @deprecated Use MySQLConnection::fetchRecords() instead.
     */
    public function fetchRecordsNonExhaustive(string $query): array
    {
        $this->query($query);

        /*
         * Normally, this would be wrapped in a do...while statement to ensure that all results are retrieved,
         * but here we only want the first result.
         */
        $rs = array();
        $result = $this->mysqli->store_result();
        if ($result) {
            while ($row = $result->fetch_object()) {
                $rs[] = $row;
            }
            $result->free();
        }
        return ($rs);
    }

    /**
     * Retrieves the value of a constant.
     * @param string $setting Name of the constant holding the setting value.
     * @param bool $required (Optional) Specify if the setting is required or not. Defaults to TRUE.
     * @return mixed
     * @throws ConfigurationUndefinedException
     */
    public static function getAppSetting(string $setting, bool $required = true): mixed
    {
        if (!defined($setting)) {
            if ($required === false) {
                return null;
            }
            throw new ConfigurationUndefinedException("$setting not found in app settings.");
        }
        return (constant($setting));
    }

    /**
     * Returns a generic object with database settings. If no settings are supplied,
     * it will use default app settings.
     * @param string $host Database host. Empty string to use app settings.
     * @param string $user Database user. Empty string to use app settings.
     * @param string $password Database password. Empty string to use app settings.
     * @param string $schema Database schema. Empty string to use app settings.
     * @param string $port Database port. Empty string to use app settings.
     * @return DBConnectionSettings Initialized object containing database properties
     * @throws ConfigurationUndefinedException
     */
    protected static function getConnectionSettings(string $host = '', string $user = '', string $password = '', string $schema = '', string $port = ''): object
    {
        return new DBConnectionSettings(
            $host ?: self::getAppSetting('MYSQL_HOST'),
            $user ?: self::getAppSetting('MYSQL_USER'),
            $password ?: self::getAppSetting('MYSQL_PASS'),
            $schema ?: self::getAppSetting('MYSQL_SCHEMA'),
            $port ?: self::getAppSetting('MYSQL_PORT', false)
        );
    }

    /**
     * Returns a MySQLi object using either default connection settings or settings passed in.
     * @param string $host (Optional) database connection host
     * @param string $user (Optional) database connection user name
     * @param string $password (Optional) database connection password
     * @param string $schema (Optional) database name
     * @param string $port (Optional) database connection port
     * @return mysqli
     * @throws ConfigurationUndefinedException
     */
    public static function getMySQLiInstance(
        string $host = '',
        string $user = '',
        string $password = '',
        string $schema = '',
        string $port = ''): mysqli
    {
        $c = static::getConnectionSettings($host, $user, $password, $schema, $port);
        return (new mysqli($c->host, $c->user, $c->password, $c->schema, $c->port));
    }

    /**
     * Return the current mysqli connection or return a new connection
     * @return mysqli
     * @throws ConfigurationUndefinedException
     */
    public function getMySQLi(): mysqli
    {
        if (!$this->hasConnection()) {
            $this->mysqli = static::getMySQLiInstance();
        }
        return $this->mysqli;
    }

    /**
     * Tests if the object currently has a viable database connection.
     * @return bool Flag indicating if there is a viable database connection or not.
     */
    public function hasConnection(): bool
    {
        if (!isset($this->mysqli)) {
            return (false);
        }
        return ($this->mysqli->connect_error === null);
    }

    /**
     * Executes SQL statement
     * @param string $query SQL statement to execute.
     * @param string $types
     * @param ...$vars
     * @throws FailedQueryException
     */
    public function query(string $query, string $types = '', ...$vars): void
    {
        try {
            $this->connectToDatabase();
        }
        catch (ConfigurationUndefinedException|ConnectionException $ex) {
            $msg = 'Connection error. [' . Log::getClassBaseName($ex::class) . ']' . $ex->getMessage();
            throw new FailedQueryException($msg);
        }
        if ($types) {
            try {
                $stmt = $this->mysqli->prepare($query);
            }
            catch(mysqli_sql_exception $ex) {
                throw new FailedQueryException('Could not prepare statement: ' . $ex->getMessage());
            }
            if (!$stmt) {
                throw new FailedQueryException('Could not prepare statement: ' . $this->mysqli->error);
            }
            $stmt->bind_param($types, ...$vars);

            if (!$stmt->execute()) {
                throw new FailedQueryException('Error executing query: ' . $this->mysqli->error);
            }
            $stmt->close();
        } else {
            try {
                $this->mysqli->query($query);
            } catch (mysqli_sql_exception $ex) {
                throw new FailedQueryException('Error executing query: ' . $ex->getMessage());
            }
        }
    }

    /**
     * Alias for MySQLConnection->connectToDatabase() for convenience.
     * Can be chained with other MySQLConnection methods.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function mysqli(): void
    {
        $this->connectToDatabase();
    }

    /**
     * Retrieves the last insert id created in the database.
     * @return int Last insert id value.
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function retrieveInsertID(): int
    {
        $data = $this->fetchRecords('SELECT LAST_INSERT_ID() as `insert_id`');
        if (1 > count($data)) {
            throw new RecordNotFoundException('Could not retrieve insert id.');
        }
        return $data[0]->insert_id;
    }

    /**
     * Copy an existing MySQL connection to the object.
     * @param mysqli $mysqli
     * @return void
     */
    public function setMySQLi(mysqli $mysqli): void
    {
        $this->mysqli = $mysqli;
    }
}