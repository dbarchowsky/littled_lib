<?php
namespace Littled\Database;

use Littled\App\AppBase;
use mysqli;


class MySQLConnection extends AppBase
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
        string $port = ''): static
    {
        $this->traitConnectToDatabase($host, $user, $password, $schema, $port);
        foreach($this as $prop) {
            if ($prop instanceof MySQLConnection) {
                $prop->shareConnection($this);
            }
        }
        return $this;
    }
}