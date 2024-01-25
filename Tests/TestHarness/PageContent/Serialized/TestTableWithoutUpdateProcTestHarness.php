<?php

namespace LittledTests\TestHarness\PageContent\Serialized;


use LittledTests\TestHarness\PageContent\Serialized\TestTableTestHarness;

class TestTableWithoutUpdateProcTestHarness extends TestTableTestHarness
{
    public function generateUpdateQuery(): ?array
    {
        return [];
    }
}