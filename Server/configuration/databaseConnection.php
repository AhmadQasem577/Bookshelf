<?php

    class DatabaseConnection {
        private $host;
        private $username;
        private $password;
        private $database;

        public function __construct($host, $username, $password, $database) {
            $this->host = $host;
            $this->username = $username;
            $this->password = $password;
            $this->database = $database;
        }

        public function connect() {
            $connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($connection->connect_error) {
                die("Connection failed: " . $connection->connect_error);
            }
            echo "Connected successfully";
            
            return $connection;
        }
    }
?>