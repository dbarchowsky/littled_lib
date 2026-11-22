<?php

namespace Littled\ETL;


class SampleRecord
{
    public int          $recordId;
    public string       $description;

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setRecordId(int $recordId): static
    {
        $this->recordId = $recordId;
        return $this;
    }
}