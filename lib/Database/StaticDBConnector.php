<?php

namespace Littled\Database;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use mysqli;

trait StaticDBConnector
{
    protected static ?MySQLConnection   $conn;

    public static function closeDatabaseConnection(): void
    {
        if (!isset(static::$conn)) {
            return;
        }
        static::$conn->closeDatabaseConnection();
    }

    /**
     * Makes a database connection if it hasn't been made yet.
     * @return MySQLConnection
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public static function connectToDatabase(): MySQLConnection
    {
        if (!isset(static::$conn) || static::$conn->getConnectionId() === null) {
            static::$conn = (new MySQLConnection())->connectToDatabase();
        }
        return static::$conn;
    }

    /**
     * Get a shared MySQL connection to avoid opening multiple connections.
     * @return MySQLConnection
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public static function getDBConnection(): MySQLConnection
    {
        return static::connectToDatabase();
    }

    /**
     * Get the object's mysqli connection.
     * @return mysqli
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function getMySQLi(): mysqli
    {
        return static::$conn->getMySQLi();
    }

    /**
     * Assign a shared MySQL connection to this object.
     * @param MySQLConnection $src
     * @return $this
     */
    public function shareConnection(MySQLConnection $src): static
    {
        if (!isset(static::$conn)) {
            static::$conn = new MySQLConnection();
        }
        static::$conn->shareConnection($src);
        return $this;
    }
}