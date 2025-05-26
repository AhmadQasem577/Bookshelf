<?php
    class User{
        private $name;
        private $email;
        private $password;
        private $createdAt;
        public function __construct($name, $email, $password, $createdAt) {
            $this->name = $name;
            $this->email = $email;
            $this->password = $password;
            $this->createdAt = $createdAt;
        }
        public function getName() {
            return $this->name;
        }
        public function getEmail() {
            return $this->email;
        }
        public function getCreatedAt() {
            return $this->createdAt;
        }
    }
?>
