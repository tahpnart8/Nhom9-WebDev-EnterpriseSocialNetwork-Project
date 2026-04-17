<?php
class AuditLog {
    private $conn;
    private $table_name = "audit_logs";

    public function __construct($db) {
        $this->conn = $db;
    }

    public static function log($db, $action_type, $entity_type, $entity_id = null, $details = null, $company_id = null) {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) return false;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $query = "INSERT INTO audit_logs (company_id, user_id, action_type, entity_type, entity_id, details, ip_address) 
                  VALUES (:cid, :uid, :at, :et, :eid, :details, :ip)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':cid', $company_id);
        $stmt->bindParam(':uid', $user_id);
        $stmt->bindParam(':at', $action_type);
        $stmt->bindParam(':et', $entity_type);
        $stmt->bindParam(':eid', $entity_id);
        $stmt->bindParam(':details', $details);
        $stmt->bindParam(':ip', $ip);
        
        return $stmt->execute();
    }

    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT a.*, u.full_name as user_name, c.company_name 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN users u ON a.user_id = u.id 
                  LEFT JOIN companies c ON a.company_id = c.id 
                  ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount() {
        return $this->conn->query("SELECT COUNT(*) FROM " . $this->table_name)->fetchColumn();
    }
}
?>
