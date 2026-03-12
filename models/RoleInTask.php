<?php
class RoleInTask {
    private $user_id;
    private $task_id;
    private $role_id;
    private $role_name;
    private $description;

    public function __construct($user_id, $task_id, $role_id, $role_name, $description) {
        $this->user_id = $user_id;
        $this->task_id = $task_id;
        $this->role_id = $role_id;
        $this->role_name = $role_name;
        $this->description = $description;
    }

    public function getUserId() { return $this->user_id; }
    public function getTaskId() { return $this->task_id; }
    public function getRoleId() { return $this->role_id; }
    public function getRoleName() { return $this->role_name; }
    public function getDescription() { return $this->description; }

    public function setRoleName($role_name) { $this->role_name = $role_name; }
    public function setDescription($description) { $this->description = $description; }
}
?>
