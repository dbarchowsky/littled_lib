<?php

namespace Littled\Database;

/**
 * MySQL connection
 */
class MySQLConnection
{
    use MySQLOperations {
        mysqli as traitMysqli;
        connectToDatabase as traitConnectToDatabase;
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
}