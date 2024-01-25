<?php

namespace LittledTests\TestHarness\PageContent\Serialized\LinkedContent;


use Littled\PageContent\Serialized\LinkedContent;

class LinkedContentUninitializedTestHarness extends LinkedContent
{
    public function generateListingsPreparedStmt(string $arg_types = '', ...$args): array
    {
        /* stub */
        return [];
    }

    function getContentLabel(): string
    {
        /* stub */
        return 'Uninitialized linked content test harness';
    }
}