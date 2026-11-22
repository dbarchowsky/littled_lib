<?php

namespace Littled\ETL;


class ETLBase extends ETLConnector
{
    public ETLMigrator      $migrator;
    public ETLValidator     $validator;
}