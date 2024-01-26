<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Database\MySQLConnection;
use Littled\Exception\NotImplementedException;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentChild;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentNameTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentNonDefaultColumn;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentTitleTestHarness;
use PHPUnit\Framework\TestCase;
use Exception;

class SerializedContentTestBase extends TestCase
{
    protected static MySQLConnection $conn;

    /**
     * @throws NotImplementedException Table name is not set in inherited classes.
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        static::$conn = new MySQLConnection();

        $query = 'DR' . 'OP TABLE IF EXISTS `' . SerializedContentChild::getTableName() . '`';
        static::$conn->query($query);

        $query = 'CRE' . 'ATE TABLE `' . SerializedContentChild::getTableName() . '` (' .
            '`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,' .
            '`vc_col1` VARCHAR(50),' .
            '`vc_col2` VARCHAR(255),' .
            '`int_col` INT,' .
            '`bool_col` BOOLEAN,' .
            '`date_col` DATETIME);';
        static::$conn->query($query);

        $query = 'DR' . 'OP TABLE IF EXISTS `' . SerializedContentTitleTestHarness::getTableName();
        static::$conn->query($query);

        $query = 'CRE' . 'ATE TABLE `' . SerializedContentTitleTestHarness::getTableName() . '` (' .
            '`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,' .
            '`title` VARCHAR(50),' .
            '`vc_col` VARCHAR(255),' .
            '`int_col` INT);';
        static::$conn->query($query);

        $query = 'DR' . 'OP TABLE IF EXISTS `' . SerializedContentNameTestHarness::getTableName();
        static::$conn->query($query);

        $query = 'CRE' . 'ATE TABLE `' . SerializedContentNameTestHarness::getTableName() . '` (' .
            '`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,' .
            '`name` VARCHAR(50),' .
            '`vc_col` VARCHAR(255),' .
            '`bool_col` BOOLEAN,' .
            '`date_col` DATE);';
        static::$conn->query($query);

        $query = 'DR' . 'OP TABLE IF EXISTS `' . SerializedContentNonDefaultColumn::getTableName();
        static::$conn->query($query);

        $query = 'CRE' . 'ATE TABLE `' . SerializedContentNonDefaultColumn::getTableName() . '` (' .
            '`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,' .
            '`name` VARCHAR(50),' .
            '`vc_col` VARCHAR(255),' .
            '`bool_col` BOOLEAN,' .
            '`date_col` DATE,' .
            '`non_default` VARCHAR(50));';
        static::$conn->query($query);

        $query = 'DROP PROCEDURE IF EXISTS `testListSelect`;';
        static::$conn->query($query);

        $query = 'CRE' . 'ATE PROCEDURE `testListSelect`() ' .
            'BEGIN ' .
            'SEL' . 'ECT * from `' . SerializedContentTitleTestHarness::getTableName() . '` ' .
            'ORDER BY `title`; ' .
            'END;';
        static::$conn->query($query);

        static::$conn->query('INS' . 'ERT INTO `' . SerializedContentTitleTestHarness::getTableName() . '` (`title`,`vc_col`,`int_col`) VALUES (\'test one\',\'foo\',67);');
        static::$conn->query('INS' . 'ERT INTO `' . SerializedContentTitleTestHarness::getTableName() . '` (`title`,`vc_col`,`int_col`) VALUES (\'test two\',\'bar\',860);');
        static::$conn->query('INS' . 'ERT INTO `' . SerializedContentTitleTestHarness::getTableName() . '` (`title`,`vc_col`,`int_col`) VALUES (\'test three\',\'biz\',1032);');
        static::$conn->query('INS' . 'ERT INTO `' . SerializedContentTitleTestHarness::getTableName() . '` (`title`,`vc_col`,`int_col`) VALUES (\'test four\',\'bash\',94);');
    }

    /**
     * @throws NotImplementedException Table name is not set in inherited classes.
     * @throws Exception
     */
    public static function tearDownAfterClass(): void
    {
        static::$conn->query('DROP PROCEDURE IF EXISTS `testListSelect`');
        static::$conn->query('D' . 'ROP TABLE `' . SerializedContentChild::getTableName() . '`');
        static::$conn->query('D' . 'ROP TABLE `' . SerializedContentNameTestHarness::getTableName() . '`');
        static::$conn->query('D' . 'ROP TABLE `' . SerializedContentTitleTestHarness::getTableName() . '`');
        static::$conn->query('D' . 'ROP TABLE `' . SerializedContentNonDefaultColumn::getTableName() . '`');
    }

}