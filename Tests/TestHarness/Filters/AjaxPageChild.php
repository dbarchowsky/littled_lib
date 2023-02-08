<?php

namespace Littled\Tests\TestHarness\Filters;

use DateTime;
use Littled\Ajax\AjaxPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;

class AjaxPageChild extends AjaxPage
{
    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     */
    function throwError()
    {
        $this->collectContentProperties();
    }
}