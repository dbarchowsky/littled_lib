<?php

namespace Littled\Tests\PageContent\Serialized\TestObjects;

use Littled\PageContent\Serialized\SerializedContent;

/**
 * Implements abstract classes in SerializedContent so the parent class can be used in unit tests.
 */
class SerializedContentTestHarness extends SerializedContent
{
    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        return array();
    }
}