<?php
class Conversation {
    private $id;
    private $created_at;
    private $updated_at;

    public function __construct($id, $created_at, $updated_at) {
        $this->id = $id;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getId() { return $this->id; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
}
?>
