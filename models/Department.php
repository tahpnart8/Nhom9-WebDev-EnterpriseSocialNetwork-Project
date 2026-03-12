<?php
class Department {
    private $id;
    private $dept_name;
    private $description;

    public function __construct($id, $dept_name, $description) {
        $this->id = $id;
        $this->dept_name = $dept_name;
        $this->description = $description;
    }

    public function getId() { return $this->id; }
    public function getDeptName() { return $this->dept_name; }
    public function getDescription() { return $this->description; }

    public function setDeptName($dept_name) { $this->dept_name = $dept_name; }
    public function setDescription($description) { $this->description = $description; }
}
?>
