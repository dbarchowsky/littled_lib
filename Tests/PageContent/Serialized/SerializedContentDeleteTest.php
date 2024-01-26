<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentChild;

class SerializedContentDeleteTest extends SerializedContentTestBase
{
    /**
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testDelete()
    {
        $obj = new SerializedContentChild();

        /* test valid id value */
        $obj->id->setInputValue(null);
        $obj->vc_col1->setInputValue('bar');
        $obj->save();
        $result = $obj->delete();
        $this->assertMatchesRegularExpression("/has been deleted/", $result);
    }

    /**
     * @throws ContentValidationException
     * @throws NotImplementedException
     */
    public function testDeleteDefaultIDValue()
    {
        $obj = new SerializedContentChild();

        /* test default id value (null) */
        $this->expectException(ContentValidationException::class);
        $obj->delete();
    }

    /**
     * @throws ContentValidationException
     * @throws NotImplementedException
     */
    public function testDeleteInvalidIDValue()
    {
        $obj = new SerializedContentChild();

        /* test invalid id value */
        $obj->id->setInputValue(0);
        $this->expectException(ContentValidationException::class);
        $obj->delete();
    }

    /**
     * @throws ContentValidationException
     * @throws NotImplementedException
     */
    public function testDeleteNonexistentID()
    {
        $obj = new SerializedContentChild();

        /* test invalid id value */
        $obj->id->setInputValue(997799);
        $status = $obj->delete();
        $this->assertMatchesRegularExpression("/could not be found/", $status);
    }
}