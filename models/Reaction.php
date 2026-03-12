<?php
class Reaction {
    private $id;
    private $user_id;
    private $reaction_name;

    public function __construct($id, $user_id, $reaction_name) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->reaction_name = $reaction_name;
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getReactionName() { return $this->reaction_name; }

    public function setReactionName($reaction_name) { $this->reaction_name = $reaction_name; }
}
?>
