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
        int|null $port = null): static
    {
        $this->traitConnectToDatabase($host, $user, $password, $schema, $port);
        foreach($this as $prop) {
            if ($prop instanceof MySQLConnection) {
                $prop->withConnection($this);
            }
        }
        return $this;
    }
}