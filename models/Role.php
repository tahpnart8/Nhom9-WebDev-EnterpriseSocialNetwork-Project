<?php

class Role {
    private $roleName;
    private $description;

    public function __construct($roleName, $description) {
        $this->roleName = $roleName;
        $this->description = $description;
    }

    public function getRoleName() {
        return $this->roleName;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }
}
?>
