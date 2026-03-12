<?php
class Profile {
    private $id;
    private $username;
    private $full_name;
    private $phone;
    private $email;
    private $birthdate;
    private $role;

    public function __construct($id, $username, $full_name, $phone, $email, $birthdate, $role) {
        $this->id = $id;
        $this->username = $username;
        $this->full_name = $full_name;
        $this->phone = $phone;
        $this->email = $email;
        $this->birthdate = $birthdate;
        $this->role = $role;
    }

    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getFullName() { return $this->full_name; }
    public function getPhone() { return $this->phone; }
    public function getEmail() { return $this->email; }
    public function getBirthdate() { return $this->birthdate; }
    public function getRole() { return $this->role; }

    public function setFullName($full_name) { $this->full_name = $full_name; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setEmail($email) { $this->email = $email; }
    public function setRole($role) { $this->role = $role; }
}
?>
