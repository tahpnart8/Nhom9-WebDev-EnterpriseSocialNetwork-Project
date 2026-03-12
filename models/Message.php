<?php
class Message {
    private $id;
    private $conversation_id;
    private $sender_id;
    private $content;

    public function __construct($id, $conversation_id, $sender_id, $content) {
        $this->id = $id;
        $this->conversation_id = $conversation_id;
        $this->sender_id = $sender_id;
        $this->content = $content;
    }

    public function getId() { return $this->id; }
    public function getConversationId() { return $this->conversation_id; }
    public function getSenderId() { return $this->sender_id; }
    public function getContent() { return $this->content; }

    public function setContent($content) { $this->content = $content; }
}
?>
