<?php
namespace LittledTests\DataProvider\Validation;

class CollectBooleanRequestVarTestDataProvider
{
    public static function collectBooleanRequestVarTestProvider():array
    {
        return array(
            array(new CollectBooleanRequestVarTestData(true, 'boolTest', array('boolTest' => true))),
            array(new CollectBooleanRequestVarTestData(true, 'boolTest', array('boolTest' => 1))),
            array(new CollectBooleanRequestVarTestData(true, 'boolTest', array('boolTest' => '1'))),
            array(new CollectBooleanRequestVarTestData(true, 'boolTest', array('boolTest' => 'on'))),
            array(new CollectBooleanRequestVarTestData(true, 'boolTest', array('boolTest' => 'yes'))),
            array(new CollectBooleanRequestVarTestData(true, 'boolTest', array('boolTest' => 'true'))),
            array(new CollectBooleanRequestVarTestData(false, 'boolTest', array('boolTest' => false))),
            array(new CollectBooleanRequestVarTestData(false, 'boolTest', array('boolTest' => 0))),
            array(new CollectBooleanRequestVarTestData(false, 'boolTest', array('boolTest' => '0'))),
            array(new CollectBooleanRequestVarTestData(false, 'boolTest', array('boolTest' => 'off'))),
            array(new CollectBooleanRequestVarTestData(false, 'boolTest', array('boolTest' => 'no'))),
            array(new CollectBooleanRequestVarTestData(false, 'boolTest', array('boolTest' => 'false'))),
            array(new CollectBooleanRequestVarTestData(null, 'boolTest', array('boolTest' => 'foo'))),
            array(new CollectBooleanRequestVarTestData(null, 'boolTest', array('boolTest' => 28))),
            array(new CollectBooleanRequestVarTestData(null, 'boolTest', array('na' => true))),
        );
    }
}