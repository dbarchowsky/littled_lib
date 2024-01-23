<?php

namespace LittledTests\TestHarness\PageContent\Serialized\LinkedContent;


use Littled\PageContent\Serialized\LinkedContent;

class LinkedContentUninitializedTestHarness extends LinkedContent
{
    public function generateListingsPreparedStmt(): array
    {
        /* stub */
        return [];
    }

    function getLabel(): string
    {
        /* stub */
        return 'Uninitialized linked content test harness';
    }
}