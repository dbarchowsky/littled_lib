<?php


namespace Littled\Database;


class DBConnectionSettings
{
    /** @var string Database host name */
    public string $host;
    /** @var string Database schema name */
    public string $schema;
    /** @var string Username used to establish databas connection */
    public string $user;
    /** @var string Database password */
    public string $password;
    /** @var int|null Database port number */
    public int|null $port;

    /**
     * DBConnectionSettings constructor.
     * @param string $host (Optional) Initial host name value
     * @param string $user (Optional) Initial database user name value
     * @param string $password (Optional) Initial password value
     * @param string $schema (Optional) Initial schema value
     * @param int|null $port (Optional) Initial port value
     */
    function __construct( string $host='', string $user='', string $password='', string $schema='', ?int $port=null)
    {
        $this->host = $host;
        $this->schema = $schema;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
    }
}