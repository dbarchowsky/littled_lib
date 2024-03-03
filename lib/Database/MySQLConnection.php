<?php

namespace Littled\Database;

use Littled\App\AppBase;
use mysqli;

/**
 * MySQL connection
 */
class MySQLConnection extends AppBase
{
    use MySQLOperations {
        mysqli as traitMysqli;
        connectToDatabase as traitConnectToDatabase;
        setMySQLi as traitSetMySQLi;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function connectToDatabase(
        string $host = '',
        string $user = '',
        string $password = '',
        string $schema = '',
        string $port = ''): MySQLConnection
    {
        $this->traitConnectToDatabase($host, $user, $password, $schema, $port);
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function mysqli(): MySQLConnection
    {
        $this->traitMysqli();
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this;
     */
    public function setMySQLi(mysqli $mysqli): MySQLConnection
    {
        $this->traitSetMySQLi($mysqli);
        return $this;
    }
}