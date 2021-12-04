<?php
namespace Littled\Tests\App;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\App\AppBase;
use Exception;

class AppBaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testGenerateRequestId()
    {
        $id_1 = AppBase::generateUniqueToken(30);
        $this->assertEquals(30, strlen($id_1));

        $id_2 = AppBase::generateUniqueToken(29);
        $this->assertEquals(29, strlen($id_2));

        $id_3 = AppBase::generateUniqueToken(14);
        $this->assertEquals(14, strlen($id_3));

        $id_4 = AppBase::generateUniqueToken(30);
        $this->assertNotEquals($id_1, $id_4);
    }
}