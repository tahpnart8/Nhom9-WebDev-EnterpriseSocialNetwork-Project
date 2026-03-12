<?php

class User {
    private $userName;
    private $fullName;
    private $email;
    private $phone;
    private $birthdate;

    public function __construct($userName, $fullName, $email, $phone, $birthdate) {
        $this->userName = $userName;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phone = $phone;
        $this->birthdate = $birthdate;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getBirthdate() {
        return $this->birthdate;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }
}
?>
