<?php

namespace LittledTests\TestHarness\App;


use Littled\App\LittledGlobals;

class LittledGlobalsTestHarness extends LittledGlobals
{
    protected static string $app_base_dir = '/path/to/app/';
}