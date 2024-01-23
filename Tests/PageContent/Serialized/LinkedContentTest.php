<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use LittledTests\DataProvider\PageContent\Serialized\LinkedContentTestData;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentUninitializedTestHarness;
use PHPUnit\Framework\TestCase;
use Exception;

class LinkedContentTest extends TestCase
{
    protected static bool $initialized = false;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (!static::$initialized) {
            $o = new LinkedContentTestHarness();
            $o->setUpTestData();
            static::$initialized = true;
        }
    }

    /**
     * @throws Exception
     */
    public static function tearDownAfterClass(): void
    {
        $o = new LinkedContentTestHarness();
        $o->tearDownTestData();
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\LinkedContentTestDataProvider::collectRequestTestDataProvider()
     * @param LinkedContentTestData $data
     * @return void
     */
    public function testCollectRequestData(LinkedContentTestData $data)
    {
        $o = new LinkedContentTestHarness();
        $saved_post = static::setUpPostData($o, $data);
        $o->collectRequestData();
        self::assertEquals($data->primary_id, $o->primary_id->value);
        if (!is_array($data->foreign_id)) {
            $data->foreign_id = [$data->foreign_id];
        }
        self::assertEqualsCanonicalizing($data->foreign_id, $o->foreign_id->value);
        self::assertEquals($data->label, $o->label->value);
        $_POST = $saved_post;
    }

    /**
     * @throws NotInitializedException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException|InvalidQueryException
     * @throws InvalidValueException
     */
    public function testCommitSingleLinkWithArrayValue()
    {
        $o = new LinkedContentTestHarness();
        $primary_id = 45;
        $link_id = 108;
        $start_count = $this->getLinkCount($primary_id);

        $o->setPrimaryId($primary_id)->setLinkId([13, 12, $link_id])->commitSingleLink($link_id);
        self::assertEquals($start_count+1, $this->getLinkCount($primary_id));

        // cleanup
        $o->deleteLink($link_id);
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws NotInitializedException
     * @throws ConfigurationUndefinedException|InvalidQueryException
     */
    public function testCommitSingleLinkWithBadLinkValue()
    {
        $o = new LinkedContentTestHarness();
        $primary_id = 45;
        $bad_link_id = 72945;
        self::expectException(InvalidValueException::class);
        $o->setPrimaryId($primary_id)->setLinkId([13, 12, 108])->commitSingleLink($bad_link_id);
    }

    /**
     * @throws NotInitializedException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException|InvalidQueryException
     * @throws InvalidValueException
     */
    public function testCommitSingleLinkWithSingleValue()
    {
        $o = new LinkedContentTestHarness();
        $primary_id = 45;
        $link_id = 108;
        $start_count = $this->getLinkCount($primary_id);

        $o->setPrimaryId($primary_id)->setLinkId($link_id)->commitSingleLink($link_id);
        self::assertEquals($start_count+1, $this->getLinkCount($primary_id));

        // cleanup
        $o->deleteLink($link_id);
    }

    /**
     * @throws NotInitializedException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function testCommitSingleLinkWithUpdate()
    {
        $o = new LinkedContentTestHarness();
        $primary_id = LinkedContentTestHarness::CREATE_LINK_IDS['parent1'];
        $link_id = 108;
        $new_label = 'new value';

        $o->setPrimaryId($primary_id)->setLinkId($link_id)->commitSingleLink($link_id);
        $o->label->value = $new_label;
        $o->commitSingleLink($link_id);

        $label_col = $o->label->getColumnName('label');
        $query = 'SEL'."ECT `$label_col`".
            ' FROM `'.$o::getTableName().'`'.
            ' WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ?'.
            ' AND `'.$o->foreign_id->getColumnName('link_id').'` = ?';
        $result = $o->fetchRecords($query, 'ii', $o->primary_id->value, $link_id);
        self::assertCount(1, $result);
        self::assertEquals($new_label, $result[0]->$label_col);

        // cleanup
        $o->delete();
    }

    /**
     * @throws NotInitializedException
     */
    public function testContainsLinkId()
    {
        $o = new LinkedContentTestHarness();
        $o->setLinkId(25);
        self::assertTrue($o->containsLinkId_public(25));
        self::assertFalse($o->containsLinkId_public(26));

        $o->setLinkId([45, 47, 93]);
        self::assertTrue($o->containsLinkId_public(47));
        self::assertFalse($o->containsLinkId_public(48));
    }

    /**
     * @throws ConnectionException
     * @throws NotImplementedException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws NotInitializedException|InvalidValueException
     */
    public function testDelete()
    {
        $primary_id = LinkedContentTestHarness::CREATE_LINK_IDS['parent1'];
        $o = (new LinkedContentTestHarness())
            ->setPrimaryId($primary_id)
            ->setLinkId([13, 14, 108]);

        $o->save();
        self::assertEquals(3, static::getLinkCount($primary_id));

        $o->delete();
        self::assertEquals(0, static::getLinkCount($primary_id));
    }

    /**
     * @throws InvalidQueryException
     * @throws ContentValidationException
     * @throws NotInitializedException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     */
    public function testDeleteLink()
    {
        $primary_id = LinkedContentTestHarness::CREATE_LINK_IDS['parent1'];
        $link1_id = 13;
        $link2_id = 109;
        $del_link_id = 108;
        $o = (new LinkedContentTestHarness())
            ->setPrimaryId($primary_id)
            ->setLinkId([$link1_id, $del_link_id, $link2_id]);
        $o->save();

        try {
            $o->deleteLink($link2_id+100);
            self::fail('Expected InvalidValueException not thrown.');
        }
        catch(InvalidValueException $e) {
            self::assertTrue(true);
        }

        $o->deleteLink($del_link_id);
        self::assertEquals(2, static::getLinkCount($primary_id));

        $query = 'SEL'.'ECT `'.$o->foreign_id->getColumnName('link_id').'` FROM `'.$o::getTableName().'`'.
            ' WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ?'.
            ' AND `'.$o->foreign_id->getColumnName('primary_id').'` NOT IN (?,?)';
        $result = $o->fetchRecords($query, 'iii', $primary_id, $link1_id, $link2_id);
        self::assertCount(0, $result);

        // cleanup
        $o->delete();
    }

    /**
     * @throws NotImplementedException|NotInitializedException
     * @throws ContentValidationException|InvalidQueryException
     * @throws Exception
     */
    public function testDeleteStaleLinks()
    {
        $primary_id = 45;
        $o = new LinkedContentTestHarness();

        // retrieve the initial count
        $original_count = static::getLinkCount($primary_id);
        self::assertEquals(0, $original_count);

        // populate with test link records
        $o->setPrimaryId($primary_id)->setLinkId([108,109,13])->save();
        self::assertEquals($original_count+3, static::getLinkCount($primary_id));

        // remove link to FK with value of 109
        $o->deleteStaleLinks([108,13]);
        self::assertEquals($original_count+2, static::getLinkCount($primary_id));

        // clean up
        $o->delete();
    }

    /**
     * @throws NotImplementedException
     */
    public function testFormatRecordLookupQuery()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(3);
        $o->foreign_id->setInputValue(23);
        $expected = '/^SELECT .*`'.LinkedContentTestHarness::getTableName().
            '` WHERE `'.$o->primary_id->getColumnName('primary_id').'` = \? '.
            'AND `'.$o->foreign_id->getColumnName('foreign_id').'` = \?/';
        list($query, $arg_types, $args) =
            $o->formatRecordLookupQuery_public(
                'SEL'.'ECT COUNT(1) AS `count` FROM `'.LinkedContentTestHarness::getTableName().'`');
        self::assertMatchesRegularExpression($expected, $query);
        self::assertEquals('ii', $arg_types);
        self::assertIsArray($args);
        self::assertCount(2, $args);
        self::assertContains(3, $args);
        self::assertContains(23, $args);
    }

    public function testGetLinkedProperties()
    {
        $o = new LinkedContentTestHarness();
        $properties = $o->getLinkedProperties_public();
        self::assertIsArray($properties);
        self::assertGreaterThan(0, count($properties));
        self::assertContains('primary_id', $properties);
        self::assertContains('foreign_id', $properties);
        self::assertNotContains('label', $properties);
    }

    /**
     * @throws Exception
     */
    public function testInsertUpdateAndDelete()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::CREATE_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::CREATE_LINK_IDS['parent2']);

        // confirm there is no pre-existing record
        $result = static::lookupRecord($o);
        self::assertEquals(0, $result[0]->count);

        $o->save();

        // confirm record after saving it
        $result = static::lookupRecord($o);
        self::assertEquals(1, $result[0]->count);

        $result = static::lookupLabel($o);
        self::assertCount(1, $result);
        self::assertEquals('', $result[0]->label);

        $label = 'my label';
        $o->label->setInputValue($label);
        $o->save();

        // confirm the existing record was updated
        $result = static::lookupRecord($o);
        self::assertEquals(1, $result[0]->count);

        // confirm that the new label value was saved
        $result = static::lookupLabel($o);
        self::assertCount(1, $result);
        self::assertEquals($label, $result[0]->label);

        $o->delete();

        // confirm that the record has been removed
        $result = static::lookupRecord($o);
        self::assertEquals(0, $result[0]->count);
    }

    /**
     * @throws Exception
     */
    public function testRead()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent2']);
        $o->read();
        self::assertEquals(LinkedContentTestHarness::EXISTING_LINK_IDS['label'], $o->label->value);
    }

    /**
     * @return void
     * @throws NotImplementedException|Exception
     */
    public function testReadNonexistentRecord()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2']);
        try {
            $o->read();
            self::fail('Expected RecordNotFoundException not thrown.');
        } catch(RecordNotFoundException $e) {
            self::assertMatchesRegularExpression('/'.LinkedContentTestHarness::getTableName().'.* not found/', $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function testRecordExists()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent2']);
        self::assertTrue($o->recordExists());

        $o->primary_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1']);
        self::assertFalse($o->recordExists());
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws NotInitializedException|InvalidValueException
     */
    public function testSave()
    {
        $parent_id = 45;
        $link_id = 109;
        $label = 'test value';
        $o = new LinkedContentTestHarness();
        $o->label->setInputValue($label);
        $o->setPrimaryId($parent_id)->setLinkId($link_id);

        $start_count = $this::getLinkCount($parent_id, $link_id);
        self::assertEquals(0, $start_count);

        $o->save();
        self::assertEquals($start_count+1, $this::getLinkCount($parent_id, $link_id));

        $label_col = $o->label->getColumnName('label');
        $query = 'SEL'."ECT `$label_col` FROM `".$o::getTableName().'` '.
            ' WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ?'.
            ' AND `'.$o->foreign_id->getColumnName('link_id').'` = ?';
        $result = $o->fetchRecords($query, 'ii', $parent_id, $link_id);
        self::assertGreaterThan(0, count($result));
        self::assertEquals($label, $result[0]->$label_col);

        // cleanup
        $o->delete();
    }

    /**
     * @throws NotInitializedException
     */
    public function testSetLinkIdAsInteger()
    {
        $o = new LinkedContentTestHarness();
        self::assertNull($o->foreign_id->value);
        $new_id = 14;
        $o->setLinkId($new_id);
        self::assertEquals($new_id, $o->foreign_id->value);
    }

    /**
     * @throws NotInitializedException
     */
    public function testSetLinkIdAsArray()
    {
        $o = new LinkedContentTestHarness();
        self::assertNull($o->foreign_id->value);
        $new_id = [14, 108, 109];
        $o->setLinkId($new_id);
        self::assertEqualsCanonicalizing($new_id, $o->foreign_id->value);
    }

    public function testSetForeignIdWhenUninitialized()
    {
        $o = new LinkedContentUninitializedTestHarness();
        try {
            $o->setLinkId(12);
            self::fail('Expected NotInitializedException not thrown.');
        }
        catch(NotInitializedException $e) {
            $expected = '/link.* not initialized/i';
            self::assertMatchesRegularExpression($expected, $e->getMessage());
        }
    }

    public function testSetPrimaryIdWhenUninitialized()
    {
        $o = new LinkedContentUninitializedTestHarness();
        try {
            $o->setPrimaryId(25);
            self::fail('Expected NotInitializedException not thrown.');
        }
        catch(NotInitializedException $e) {
            $expected = '/primary.* not initialized/i';
            self::assertMatchesRegularExpression($expected, $e->getMessage());
        }
    }

    /**
     * @throws NotInitializedException
     */
    public function testSetPrimaryId()
    {
        $o = new LinkedContentTestHarness();
        self::assertNull($o->primary_id->value);
        $new_id = 12;
        $o->setPrimaryId($new_id);
        self::assertEquals($new_id, $o->primary_id->value);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\LinkedContentTestDataProvider::validateInputFailDataTestProvider()
     * @param LinkedContentTestData $data
     * @return void
     */
    public function testValidateInputFail(LinkedContentTestData $data)
    {
        $o = new LinkedContentTestHarness();
        $saved_post = static::setUpPostData($o, $data);
        $o->collectRequestData();
        self::expectException(ContentValidationException::class);
        $o->validateInput();
        $_POST = $saved_post;
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\LinkedContentTestDataProvider::validateInputPassDataTestProvider()
     * @param LinkedContentTestData $data
     * @return void
     * @throws ContentValidationException
     */
    public function testValidateInputPass(LinkedContentTestData $data)
    {
        $o = new LinkedContentTestHarness();
        if ($data->required) {
            $o->foreign_id->setAsRequired();
        } else {
            $o->foreign_id->setAsNotRequired();
        }
        $saved_post = static::setUpPostData($o, $data);
        $o->collectRequestData();
        $o->validateInput();
        self::assertFalse($o->hasValidationErrors());
        $_POST = $saved_post;
    }

    /**
     * @param ?int $primary_id
     * @param ?int $link_id
     * @return int
     * @throws NotImplementedException
     * @throws Exception
     */
    public static function getLinkCount(?int $primary_id=null, ?int $link_id=null): int
    {
        $o = new LinkedContentTestHarness();
        $arg_types = '';
        $args = [];
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.LinkedContentTestHarness::getTableName().'`';
        if ($primary_id === NULL) {
            $result = $o->fetchRecords($query);
        } else {
            $args[] = $primary_id;
            $arg_types .= 'i';
            $query .= ' WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ?';
            if ($link_id > 1) {
                $args[] = $link_id;
                $arg_types .= 'i';
                $query .= ' AND `'.$o->foreign_id->getColumnName('foreign_id').'` = ?';
            }
            $result = $o->fetchRecords($query, $arg_types, ...$args);
        }
        return $result[0]->count;
    }

    /**
     * @throws Exception
     */
    protected static function lookupRecord(LinkedContentTestHarness $o): array
    {
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.$o::getTableName().'` '.
            'WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ? '.
            'AND `'.$o->foreign_id->getColumnName('foreign_id').'` = ?';
        return $o->fetchRecords($query, 'ii', $o->primary_id->value, $o->foreign_id->value);
    }

    /**
     * @throws NotImplementedException
     * @throws Exception
     */
    protected static function lookupLabel(LinkedContentTestHarness $o): array
    {
        $query = 'SEL'.'ECT `label` FROM `'.$o::getTableName().'` '.
            'WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ? '.
            'AND `'.$o->foreign_id->getColumnName('foreign_id').'` = ?';
        return $o->fetchRecords($query, 'ii', $o->primary_id->value, $o->foreign_id->value);
    }

    protected static function setUpPostData(LinkedContentTestHarness $o, LinkedContentTestData $data): array
    {
        $saved_post = $_POST;
        $_POST = array_merge($_POST, array(
            $o->primary_id->key => $data->primary_id,
            $o->foreign_id->key => $data->foreign_id,
            $o->label->key => $data->label,
        ));
        return $saved_post;
    }
}