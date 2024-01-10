<?php
namespace LittledTests\PageContent\Album;

use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Albums\ImageFormat;
use PHPUnit\Framework\TestCase;


class ImageFormatTest extends TestCase
{
    function testConstructor()
    {
        $if = new ImageFormat();
        $this->assertTrue($if->site_section_id->required);
        $this->assertTrue($if->size_id->required);
        $this->assertFalse($if->format->required);
        $this->assertGreaterThan(7, $if->format->size_limit);

        $this->assertEquals('', $if->getSectionName());
        $this->assertEquals('', $if->getSizeName());
    }

    /**
     * @throws RecordNotFoundException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    function testRetrieveRecord()
    {
        $if = new ImageFormat();
        $if->id->value = $this::fetchSampleRecordId($if);
        $if->read();
        $this->assertGreaterThan(0, $if->site_section_id->value);
        $this->assertNotEquals('', $if->getSectionName());
        $this->assertGreaterThan(0, $if->size_id->value);
        $this->assertNotEquals('', $if->getSizeName());
    }

    /**
     * @throws RecordNotFoundException
     * @throws NotImplementedException
     * @throws Exception
     */
    protected function fetchSampleRecordId(MySQLConnection $conn): int
    {
        $query = 'SEL'.'ECT `id` FROM `'.ImageFormat::getTableName().'` LIMIT 1';
        $data = $conn->fetchRecords($query);
        if (count($data) < 1) {
            throw new RecordNotFoundException('Could not retrieve test image format record.');
        }
        return $data[0]->id;
    }
}