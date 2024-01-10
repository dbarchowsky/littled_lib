<?php
namespace LittledTests\TestHarness\App;

use Littled\App\AppBase;


class AppBaseTestHarness extends AppBase
{
	protected static string $error_page_url = '/subclass/error/route';
	protected static string $error_key      = 'subErr';

    /**
     * @return string
     */
    public static function getAjaxInputStream(): string
    {
        return parent::getAjaxInputStream();
    }
}