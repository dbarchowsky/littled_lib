<?php


namespace Littled\Database;


class DBConnectionSettings
{
    /** @var string Database host name */
    protected string       $host;
    /** @var string Database schema name */
    protected string       $schema;
    /** @var string Username used to establish database connection */
    protected string       $user;
    /** @var string Database password */
    protected string       $password;
    /** @var int|null Database port number */
    protected int|null     $port;
    public string          $aes_key = '';

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

    public function aes_key(): string
    {
        return $this->aes_key;
    }

    public function schema(): string
    {
        return $this->schema;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function password(): string
    {
        return $this->password;
    }
    public function port(): int|null
    {
        return $this->port ?? null;
    }
    public function user(): string
    {
        return $this->user;
    }
    public function setAESKey(string $key): static
    {
        $this->aes_key = $key;
        return $this;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;
        return $this;
    }

    public function setSchema(string $schema): static
    {
        $this->schema = $schema;
        return $this;
    }

    public function setUser(string $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function setPort(int|null $port=null): static
    {
        $this->port = $port;
        return $this;
    }
}