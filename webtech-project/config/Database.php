<?php
class Database {
    private $host = 'localhost';
    private $dbname = 'online_quiz_platform';
    private $username = 'root';
    private $password = '';
    private $connection;

    public function connect() {
        if ($this->connection == null) {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            if ($this->connection->connect_error) {
                die("Connection failed: " . $this->connection->connect_error);
            }
        }
        return $this->connection;
    }
}
?>