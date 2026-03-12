<?php
class Post {
    private $id;
    private $author_id;
    private $task_report_id;
    private $content;
    private $channel_id;
    private $created_at;

    public function __construct($id, $author_id, $task_report_id, $content, $channel_id, $created_at) {
        $this->id = $id;
        $this->author_id = $author_id;
        $this->task_report_id = $task_report_id;
        $this->content = $content;
        $this->channel_id = $channel_id;
        $this->created_at = $created_at;
    }

    public function getId() { return $this->id; }
    public function getAuthorId() { return $this->author_id; }
    public function getTaskReportId() { return $this->task_report_id; }
    public function getContent() { return $this->content; }
    public function getChannelId() { return $this->channel_id; }
    public function getCreatedAt() { return $this->created_at; }

    public function setContent($content) { $this->content = $content; }
}
?>
