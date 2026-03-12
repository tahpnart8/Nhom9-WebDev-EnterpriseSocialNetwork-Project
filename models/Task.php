<?php

class Task {
    private $title;
    private $description;
    private $status;
    private $startDate;
    private $endDate;
    private $priority;

    public function __construct($title, $description, $status, $startDate, $endDate, $priority) {
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->priority = $priority;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function getPriority() {
        return $this->priority;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
}
?>
