<?php

namespace Littled\ETL;


class ETLBase extends ETLConnector
{
    public ETLMigrator      $migrator;
    public ETLValidator     $validator;

    public function __construct()
    {
        $this->validator = new ETLValidator()->withConnection($this);
    }

    protected function connectToLegacy(): static
    {
        parent::connectToLegacy();
        if (isset($this->migrator)) {
            $this->migrator->withLegacyConnection($this->legacyDB);
        }
        if (isset($this->validator)) {
            $this->validator->withLegacyConnection($this->legacyDB);
        }
        return $this;
    }

    public function setTableName(string $table): static
    {
        $this->table = $table;
        if (isset($this->migrator)) {
            $this->migrator->setTableName($table);
        }
        if (isset($this->validator)) {
            $this->validator->setTableName($table);
        }
        return $this;
    }
}