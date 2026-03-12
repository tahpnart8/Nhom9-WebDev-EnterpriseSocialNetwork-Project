<?php
class Comment {
    private $id;
    private $user_id;
    private $post_id;
    private $content;

    public function __construct($id, $user_id, $post_id, $content) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->post_id = $post_id;
        $this->content = $content;
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getPostId() { return $this->post_id; }
    public function getContent() { return $this->content; }

    public function setContent($content) { $this->content = $content; }
}
?>
