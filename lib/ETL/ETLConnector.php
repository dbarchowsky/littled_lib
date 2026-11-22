<?php

namespace Littled\ETL;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\FailedQueryException;
use Littled\Log\Log;


class ETLConnector extends MySQLConnection
{
    protected static string     $table;
    protected MySQLConnection   $legacyDB;

    /**
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function __construct()
    {
        $this->connectToLegacy();
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function connectToLegacy(): void
    {
        $this->legacyDB = new MySQLConnection()
            ->withSharedConnection(false)
            ->connectToDatabase(
                $_ENV['MYSQL_LEGACY_HOST'],
                $_ENV['MYSQL_USER'],
                $_ENV['MYSQL_PASS'],
                $_ENV['MYSQL_SCHEMA'],
                $_ENV['MYSQL_PORT']);
    }

    /**
     * @return string
     * @throws FailedQueryException
     */
    public function getCurrentLegacySchema(): string
    {
        $result = $this->legacyDB->fetchRecords('SELECT DATABASE() AS `schema`');
        return $result[0]->schema;
    }

    /**
     * @return string
     * @throws FailedQueryException
     */
    public function getCurrentSchema(): string
    {
        $result = $this->fetchRecords('SELECT DATABASE() AS `schema`');
        return $result[0]->schema;
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getTableName(): string
    {
        if (empty(static::$table)) {
            throw new ConfigurationUndefinedException('Table name not set in ' . Log::getClassBaseName(static::class));
        }
        return static::$table;
    }
}