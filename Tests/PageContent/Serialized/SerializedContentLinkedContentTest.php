<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Request\ForeignKeyInput;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\SerializedLinkedTestHarness;
use PHPUnit\Framework\TestCase;

class SerializedContentLinkedContentTest extends TestCase
{
    public function testGetForeignKeyPropertyList()
    {
        $o = new SerializedLinkedTestHarness();
        $fkp = $o->getForeignKeyPropertyList_public();
        self::assertCount(1, $fkp);
        self::assertInstanceOf(ForeignKeyInput::class, $fkp[0]);
        self::assertEquals(SerializedLinkedTestHarness::LINK_KEY, $fkp[0]->key);
    }
}