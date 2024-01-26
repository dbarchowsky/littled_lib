<?php

namespace LittledTests\TestHarness\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\PageContent\Serialized\SerializedContent;


/**
 * Implements abstract classes in SerializedContent so the parent class can be used in unit Tests.
 */
class SerializedContentTestHarness extends SerializedContent
{
    protected static string $table_name = 'test_table';

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        return array();
    }

    function getContentLabel(): string
    {
        /* stub */
        return 'Serialized content test harness';
    }

    /**
     * Public interface for testing purposes.
     * @param string $query
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    public function updateIdAfterCommit_public(string $query)
    {
        parent::updateIdAfterCommit($query);
    }
}