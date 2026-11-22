<?php

namespace Littled\ETL;


class ETLBase extends ETLConnector
{
    public ETLMigrator      $migrator;
    public ETLValidator     $validator;

    public function __construct()
    {
        $this->validator = new ETLValidator()->withConnection($this);
        $this->validator->legacyDB->withConnection($this->legacyDB);
    }

    protected function connectToLegacy(): static
    {
        parent::connectToLegacy();
        if (isset($this->migrator)) {
            $this->migrator->legacyDB->withConnection($this->legacyDB);
        }
        if (isset($this->validator)) {
            $this->validator->legacyDB->withConnection($this->legacyDB);
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