<?php
namespace Littled\Tests\TestHarness\App;

use Littled\App\AppBase;


class AppBaseTestHarness extends AppBase
{
	protected static string $error_page_url = '/subclass/error/route';
}