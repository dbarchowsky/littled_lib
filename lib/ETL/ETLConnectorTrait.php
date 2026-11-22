<?php

namespace Littled\ETL;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\FailedQueryException;
use Littled\Log\Log;

trait ETLConnectorTrait
{
    protected MySQLConnection   $legacyDB;
    protected string            $table;


    /**
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function connectToLegacy(): static
    {
        $this->legacyDB = new MySQLConnection()
            ->withSharedConnection(false)
            ->connectToDatabase(
                $_ENV['MYSQL_LEGACY_HOST'],
                $_ENV['MYSQL_USER'],
                $_ENV['MYSQL_PASS'],
                $_ENV['MYSQL_SCHEMA'],
                $_ENV['MYSQL_PORT']);
        return $this;
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
    public function getTableName(): string
    {
        if (empty($this->table)) {
            throw new ConfigurationUndefinedException('Table name not set in ' . Log::getClassBaseName(static::class));
        }
        return $this->table;
    }

    /**
     * Table name setter.
     * @param string $table
     * @return $this
     */
    public function setTableName(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Share legacy database connection with exiting connection.
     * @param MySQLConnection $conn
     * @return $this
     */
    public function withLegacyConnection(MySQLConnection $conn): static
    {
        $this->legacyDB->withConnection($conn);
        return $this;
    }
}