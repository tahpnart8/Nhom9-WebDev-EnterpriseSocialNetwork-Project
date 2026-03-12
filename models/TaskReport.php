<?php
class TaskReport {
    private $id;
    private $task_id;
    private $file_url;
    private $description;
    private $uploaded_at;
    private $created_at;

    public function __construct($id, $task_id, $file_url, $description, $uploaded_at, $created_at) {
        $this->id = $id;
        $this->task_id = $task_id;
        $this->file_url = $file_url;
        $this->description = $description;
        $this->uploaded_at = $uploaded_at;
        $this->created_at = $created_at;
    }

    public function getId() { return $this->id; }
    public function getTaskId() { return $this->task_id; }
    public function getFileUrl() { return $this->file_url; }
    public function getDescription() { return $this->description; }
    public function getUploadedAt() { return $this->uploaded_at; }
    public function getCreatedAt() { return $this->created_at; }

    public function setFileUrl($file_url) { $this->file_url = $file_url; }
    public function setDescription($description) { $this->description = $description; }
}
?>
