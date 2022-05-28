<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;

use Littled\PageContent\Serialized\SerializedContent;


/**
 * Implements abstract classes in SerializedContent so the parent class can be used in unit Tests.
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