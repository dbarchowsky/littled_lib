<?php

namespace LittledTests\TestHarness\PageContent\Serialized;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextField;

class OneToOneLinkLCTestHarness extends SerializedContent
{
    public StringTextField      $name;
    public IntegerSelect        $status_id;
    public string               $status = '';

    protected static string $table_name = 'test_oto_parent';

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->name = new StringTextField('Name', 'totName', true, '', 50);
        $this->status_id = new IntegerSelect('Status', 'totStatus');
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        /** stub */
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        return 'One-to-one link without read procedure';
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    protected function createTables()
    {
        $queries = [
            'CRE'.'ATE TABLE `test_oto_parent` ('.
            '`id` INT PRIMARY KEY,'.
            '`name` VARCHAR(50) NULL, '.
            '`status_id` INT NULL, '.
            'CONSTRAINT `fk_oto_parent_status` FOREIGN KEY (status_id) REFERENCES test_oto_status(id) ON DELETE SET NULL);',
            'CRE'.'ATE TABLE `test_oto_status` ('.
            '`id` INT PRIMARY KEY,'.
            '`name` VARCHAR(50) NULL);',
        ];
        foreach($queries as $query) {
            $this->query($query);
        }
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    protected function dropTables()
    {
        $this->query('DR'.'OP TABLE `test_oto_parent`');
        $this->query('DR'.'OP TABLE `test_oto_link`');
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    protected function populateTables()
    {
        $queries = [
            'INS'.'ERT INTO `test_oto_status` (`id`, `name`) VALUES '.
            '(1, \'new\'), '.
            '(2, \'pending\'), '.
            '(3, \'approved\'), '.
            '(4, \'archived\');',
            'INS'.'ERT INTO `test_oto_parent` (`id`, `name`, `status_id`) VALUES '.
            '(1, \'new test\', 1), '.
            '(4, \'pending test\', 2), '.
            '(5, \'approved test\', 3), '.
            '(6, \'foo\', 3), '.
            '(8, \'new test 2\', 1), '.
            '(9, \'archived test\', 4), '.
            '(10, \'no status\', null);'
        ];
        foreach($queries as $query) {
            $this->query($query);
        }
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    public function setUpTestData()
    {
        $this->createTables();
        $this->populateTables();
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    public function tearDownTestData()
    {
        $this->dropTables();
    }
}