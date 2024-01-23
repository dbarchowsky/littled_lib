<?php

namespace LittledTests\TestHarness\PageContent\Serialized\LinkedContent;


use Littled\PageContent\Serialized\LinkedContent;
use Littled\Request\ForeignKeyInput;
use Littled\Request\IntegerTextField;
use Littled\Request\StringTextField;
use Exception;

class LinkedContentTestHarness extends LinkedContent
{
    public StringTextField $label;
    public StringTextField $extra;

    public static string $table_name = 'test_link';

    public const LINK_KEY = 'linkedId';
    public const CREATE_LINK_IDS = ['parent1' => 3, 'parent2' => 15];
    public const EXISTING_LINK_IDS = ['parent1' => 2, 'parent2' => 13, 'label' => 'my label'];
    public const NONEXISTENT_LINK_IDS = ['parent1' => 8, 'parent2' => 88];


    public function __construct()
    {
        parent::__construct();
        $this->primary_id = (
            new IntegerTextField('Parent id', SerializedLinkedTestHarness::PRIMARY_KEY, true))
                ->setColumnName('parent1_id');
        $this->foreign_id = (
            new ForeignKeyInput('Linked content', self::LINK_KEY, true))
                ->setColumnName('parent2_id')
                ->setAllowMultiple();

        $this->label = new StringTextField('Name', 'linkName', true, '', 50);
        $this->extra = (new StringTextField('Name', 'linkName', false, '', 50))
            ->setIsDatabaseField(false);
    }

    /**
     * Public interface for testing purposes.
     * @param int $link_id
     * @return bool
     */
    public function containsLinkId_public(int $link_id): bool
    {
        return parent::containsLinkId($link_id);
    }

    public function getLabel(): string
    {
        return ("Test linked content");
    }

    /**
     * @inheritDoc
     */
    public function generateListingsPreparedStmt(): array
    {
        return ['CALL linkedParent2ListingsSelect(?)', 'i', $this->primary_id->value];
    }

    /**
     * @throws Exception
     */
    protected function createProcedures()
    {
        $query = 'DELIMITER $$ '.
            'CREATE OR UPDATE PROCEDURE `linkedParent2ListingsSelect` ('.
            'IN p_parent1_id INT ) '.
            'BEGIN '.
            'SELECT l.parent2_id, '.
            '    p2.name '.
            'FROM link_test l '.
            'INNER JOIN test_parent2 p2 ON l.parent2_id = p2.id '.
            'WHERE l.parent1_id = p_parent1_id; '.
            'END $$';
        $this->query($query);
    }

    /**
     * @throws Exception
     */
    protected function createTestTables()
    {
        $queries = [
            'CRE'.'ATE TABLE `test_parent1` ('.
            '    `id` INT PRIMARY KEY,'.
            '    `name` VARCHAR(50) NULL);',
            'CRE'.'ATE TABLE `test_parent2` ('.
            '    `id` INT PRIMARY KEY,'.
            '    `name` VARCHAR(50) NULL);',
            'CRE'.'ATE TABLE `'.static::getTableName().'` ('.
            '    parent1_id INT NOT NULL,'.
            '    parent2_id INT NOT NULL,'.
            '    `label` VARCHAR(50) NULL,'.
            '    CONSTRAINT fk_test_link_parent1 FOREIGN KEY (parent1_id) REFERENCES test_parent1(id) ON DELETE CASCADE,'.
            '    CONSTRAINT fk_test_link_parent2 FOREIGN KEY (parent2_id) REFERENCES test_parent2(id) ON DELETE CASCADE);',
            'CRE'.'ATE UNIQUE INDEX ix_parent1_parent2 ON test_link (parent1_id, parent2_id);'
            ];
        foreach($queries as $query) {
            $this->query($query);
        }
    }

    /**
     * @throws Exception
     */
    protected function dropTestTables()
    {
        $queries = [
            'DR'.'OP TABLE `'.static::getTableName().'`;',
            'DR'.'OP TABLE `test_parent2`;',
            'DR'.'OP TABLE `test_parent1`;',
        ];
        foreach($queries as $query) {
            $this->query($query);
        }
    }

    /**
     * Public interface for formatRecordLookupQuery() for testing purposes
     * @param string $query
     * @return array
     */
    public function formatRecordLookupQuery_public(string $query): array
    {
        return parent::formatRecordLookupQuery($query);
    }

    /**
     * Public interface for getLinkedProperties() for testing purposes
     */
    public function getLinkedProperties_public(): array
    {
        return parent::getLinkedProperties();
    }

    /**
     * @throws Exception
     */
    protected function insertTestData()
    {
        $queries = [
            'INS'.'ERT INTO `test_parent1` '.
            '(`id`, `name`) '.
            'VALUES '.
            '(1, \'foo\'),'.
            '(2, \'bar\'),'.
            '(3, \'biz\'),'.
            '(44, \'foo foo\'),'.
            '(45, \'biz bash\'),'.
            '(47, \'pricey prince\'),'.
            '(58, \'ipsum lorem\')',
            'INS'.'ERT INTO `test_parent2` '.
            '(`id`, `name`) '.
            'VALUES '.
            '(13, \'bash\'),'.
            '(14, \'zim\'),'.
            '(108, \'zip a dee do\'),'.
            '(109, \'zip a dee day\'),'.
            '(15, \'za\');',
            'INS'.'ERT INTO `'.static::getTableName().'` (parent1_id, parent2_id, `label`) '.
            'VALUES ('.
            self::EXISTING_LINK_IDS['parent1'].', '.
            self::EXISTING_LINK_IDS['parent2'].', '.
            '\''.self::EXISTING_LINK_IDS['label'].'\'), '.
            '('.self::EXISTING_LINK_IDS['parent1'].', 109, \'test link too\'),'.
            '(1, 14, \'\');'
        ];
        foreach($queries as $query) {
            $this->query($query);
        }
    }

    /**
     * @throws Exception
     */
    public function setUpTestData()
    {
        $this->createTestTables();
        $this->insertTestData();
    }

    /**
     * @throws Exception
     */
    public function tearDownTestData()
    {
        $this->dropTestTables();
    }
}