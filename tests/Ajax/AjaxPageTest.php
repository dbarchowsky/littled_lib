<?php
namespace Littled\Tests\Ajax;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Tests\Filters\Samples\AjaxPageChild;
use PHPUnit\Framework\TestCase;
use Exception;

class AjaxPageTest extends TestCase
{
    /** @var int */
    protected const TEST_CONTENT_TYPE_ID = 2;

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    function _testErrorHandler()
    {
        $ap = null;
        try {
            $ap = new AjaxPageChild();
        }
        catch(Exception $e) { /* pass */ }
        $this->expectOutputRegex('/TypeError/');
        $ap->throwError();
    }
}