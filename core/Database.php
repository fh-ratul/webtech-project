<?php

class Database
{
    private mysqli $connection;

    public function __construct(string $host, string $user, string $pass, string $name, int $port = 3306)
    {
        $this->connection = new mysqli($host, $user, $pass, $name, $port);

        if ($this->connection->connect_error) {
            die("Connection Failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8");
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}
