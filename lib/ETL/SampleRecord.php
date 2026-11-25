<?php

namespace Littled\ETL;


class SampleRecord
{
    public string       $description;
    public int          $linkId;
    public int          $parentId;
    public int          $recordId;

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setLinkIds(int $parentId, int $linkId): static
    {
        $this->parentId = $parentId;
        $this->linkId = $linkId;
        return $this;
    }

    public function setRecordId(int $recordId): static
    {
        $this->recordId = $recordId;
        return $this;
    }
}