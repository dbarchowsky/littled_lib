<?php

namespace Littled\Database;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Log\Log;
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
     * @throws ConnectionException
     */
    public static function getDBConnection(): MySQLConnection
    {
        try {
            return static::connectToDatabase();
        } catch(ConfigurationUndefinedException $ex) {
            $msg = 'Could not connect to database. (' . Log::getClassBaseName($ex::class) . ') ' . $ex->getMessage();
            throw new ConnectionException($msg);
        }
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
     * Tests if the object currently has a viable database connection.
     * @return bool Flag indicating if there is a viable database connection or not.
     */
    public function hasConnection(): bool
    {
        return isset(static::$conn) && static::$conn->hasConnection();
    }

    /**
     * Assign a shared MySQL connection to this object.
     * @param MySQLConnection $src
     * @return $this
     * @deprecated Use StaticDBConnector->withConnection() instead
     */
    public function shareConnection(MySQLConnection $src): static
    {
        return $this->withConnection($src);
    }

    /**
     * Assign a shared MySQL connection to this object.
     * @param MySQLConnection $src
     * @return $this
     */
    public function withConnection(MySQLConnection $src): static
    {
        if (!isset(static::$conn)) {
            static::$conn = new MySQLConnection();
        }
        static::$conn->withConnection($src);
        return $this;
    }
}