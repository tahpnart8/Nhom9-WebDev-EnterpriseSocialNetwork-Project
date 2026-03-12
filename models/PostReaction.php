<?php
class PostReaction {
    private $post_id;
    private $reaction_id;
    private $type;

    public function __construct($post_id, $reaction_id, $type) {
        $this->post_id = $post_id;
        $this->reaction_id = $reaction_id;
        $this->type = $type;
    }

    public function getPostId() { return $this->post_id; }
    public function getReactionId() { return $this->reaction_id; }
    public function getType() { return $this->type; }

    public function setType($type) { $this->type = $type; }
}
?>
