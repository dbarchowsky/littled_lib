<?php

namespace Littled\Tests\DataProvider\PageContent\Serialized;

use Littled\Tests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;

class SerializedContentUtilsTestDataProvider
{
    public static function formatDatabaseColumnList(): array
    {
        $o1 = new TestTableSerializedContentTestHarness();
        $o1->name->value = '';
        $o1->date->value = '';

        $o2 = new TestTableSerializedContentTestHarness();
        $o2->name->value = null;
        $o2->int_col->setInputValue(-2);
        $o2->bool_col->setInputValue(true);
        $o2->date->setInputValue(null);

        $o3 = new TestTableSerializedContentTestHarness();
        $o3->name->value = 'my test';
        $o3->int_col->setInputValue(648);
        $o3->bool_col->setInputValue(false);
        $date = date('m/d/Y H:i:s', strtotime('Feb 13, 2022 13:30:15'));
        $o3->date->setInputValue($date);

        $o4 = new TestTableSerializedContentTestHarness();
        $date = date('m/d/Y H:i:s', strtotime('Feb 13, 2022 13:30:15'));
        $o4->date->setInputValue($date, 'Y-m-d H:i:s');

        return array(
            [array(
                'name' => "''",
                'int_col' => "NULL",
                'bool_col' => "NULL",
                'date' => "NULL",
                'slot' => "NULL"),
                $o1,
                'object $o1'],
            [array(
                'name' => "NULL",
                'int_col' => "-2",
                'bool_col' => "1",
                'date' => "NULL",
                'slot' => "NULL"),
                $o2,
                'object $o2'],
            [array(
                'name' => "'my test'",
                'int_col' => "648",
                'bool_col' => "0",
                'date' => "'2022-02-13 00:00:00'",
                'slot' => "NULL"),
                $o3,
                'object $o3'],
            [array(
                'name' => "''",
                'int_col' => "NULL",
                'bool_col' => "NULL",
                'date' => "'2022-02-13 13:30:15'",
                'slot' => "NULL"),
                $o4,
                'object $o4'],
        );
    }
}