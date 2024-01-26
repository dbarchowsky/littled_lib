<?php

namespace LittledTests\TestHarness\PageContent\Serialized;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;


class OneToOneLinkProcLCTestHarness extends OneToOneLinkLCTestHarness
{
    protected function formatRecordSelectPreparedStmt(): array
    {
        return ['CALL testOtoParentSelect(?)', 'i', $this->id->value];
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        return 'One-to-one link using read procedure';
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    protected function createProcedures()
    {
        $query = 'CRE'.'ATE OR REPLACE PROCEDURE `testOtoParentSelect` (p_id INT) BEGIN '.
            'SEL'.'ECT '.
            'p.name, '.
            'IFNULL(s.name, \'\') as `status`, '.
            'p.status_id '.
            'FROM `test_oto_parent` p '.
            'LEFT JOIN `test_oto_status` s ON p.status_id = s.id '.
            'WHERE p.id = p_id '.
            'ORDER BY IFNULL(p.status_id, 999999), p.id; '.
            'END';
        $this->query($query);
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    protected function dropProcedures()
    {
        $this->query('DR'.'OP PROCEDURE `testOtoParentSelect`');
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    public function setUpTestData()
    {
        parent::setUpTestData();
        $this->createProcedures();
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    public function tearDownTestData()
    {
        parent::tearDownTestData();
        $this->dropProcedures();
    }
}