<?php

namespace Littled\ETL;

use Littled\Database\MySQLConnection;


class ETLBase extends MySQLConnection
{
    use ETLConnectorTrait {
        connectToLegacy as private traitConnectToLegacy;
        withLegacyConnection as private traitWithLegacyConnection;
    }

    public ETLMigrator      $migrator;
    public ETLValidator     $validator;

    public function __construct()
    {
        $this->validator = new ETLValidator()->withConnection($this);
    }

    /**
     * @inheritDoc
     */
    protected function connectToLegacy(): static
    {
        $this->traitConnectToLegacy();
        if (isset($this->migrator)) {
            $this->migrator->withLegacyConnection($this->legacyDB);
        }
        if (isset($this->validator)) {
            $this->validator->withLegacyConnection($this->legacyDB);
        }
        return $this;
    }

    /**
     * Sets the table name for both the migrator and validator properties.
     * @param string $table
     * @return $this
     */
    public function setTableName(string $table): static
    {
        if (isset($this->migrator)) {
            $this->migrator->setTableName($table);
        }
        if (isset($this->validator)) {
            $this->validator->setTableName($table);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withLegacyConnection(MySQLConnection $conn): static
    {
        $this->traitWithLegacyConnection($conn);
        if (isset($this->migrator)) {
            $this->migrator->withLegacyConnection($this->legacyDB);
        }
        if (isset($this->validator)) {
            $this->validator->withLegacyConnection($this->legacyDB);
        }
        return $this;
    }
}