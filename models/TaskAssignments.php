<?php
class TaskAssignments {
    private $task_id;
    private $user_id;
    private $role_in_task;
    private $status;
    private $startdate;
    private $enddate;
    private $priority;

    public function __construct($task_id, $user_id, $role_in_task, $status, $startdate, $enddate, $priority) {
        $this->task_id = $task_id;
        $this->user_id = $user_id;
        $this->role_in_task = $role_in_task;
        $this->status = $status;
        $this->startdate = $startdate;
        $this->enddate = $enddate;
        $this->priority = $priority;
    }

    public function getTaskId() { return $this->task_id; }
    public function getUserId() { return $this->user_id; }
    public function getRoleInTask() { return $this->role_in_task; }
    public function getStatus() { return $this->status; }
    public function getStartDate() { return $this->startdate; }
    public function getEndDate() { return $this->enddate; }
    public function getPriority() { return $this->priority; }

    public function setStatus($status) { $this->status = $status; }
    public function setRoleInTask($role_in_task) { $this->role_in_task = $role_in_task; }
}
?>
