<?php
class Notification {
    private $id;
    private $user_id;
    private $title;
    private $body;
    private $is_read;
    private $created_at;
    private $deleted_at;

    public function __construct($id, $user_id, $title, $body, $is_read, $created_at, $deleted_at) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->body = $body;
        $this->is_read = $is_read;
        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getTitle() { return $this->title; }
    public function getBody() { return $this->body; }
    public function getIsRead() { return $this->is_read; }
    public function getCreatedAt() { return $this->created_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    public function setIsRead($is_read) { $this->is_read = $is_read; }
    public function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }
}
?>
