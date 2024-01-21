<?php

namespace LittledTests\TestHarness\PageContent\Serialized;


use Littled\PageContent\Serialized\SerializedContentIO;

class SerializedContentIOTestHarness extends SerializedContentIO
{
    /**
     * @inheritDoc
     */
    public function delete(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function executeInsertQuery()
    {
        /* stub */
    }

    /**
     * @inheritDoc
     */
    protected function executeUpdateQuery()
    {
        /* stub */
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        /* stub */
    }

    /**
     * @inheritDoc
     */
    function getLabel(): string
    {
        return ('Test serialized content');
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        /* stub */
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        /* stub */
    }

    /**
     * @inheritDoc
     */
    public function recordExists(): bool
    {
        /* stub */
       return false;
    }
}