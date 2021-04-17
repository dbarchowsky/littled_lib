<?php


namespace Littled\Database;


class DBConnectionSettings
{
    /** @var string Database host name */
    public $host;
    /** @var string Database schema name */
    public $schema;
    /** @var string User name used to establish databas connection */
    public $user;
    /** @var string Database password */
    public $password;
    /** @var int Database port number */
    public $port;

    /**
     * DBConnectionSettings constructor.
     * @param string $host (Optional) Initial host name value
     * @param string $user (Optional) Initial database user name value
     * @param string $password (Optional) Initial password value
     * @param string $schema (Optional) Initial schema value
     * @param int|null $port (Optional) Initial port value
     */
    function __construct( $host='', $user='', $password='', $schema='', ?int $port=null)
    {
        $this->host = $host;
        $this->schema = $schema;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
    }
}