<?php
/**
 * BaseModel — Lớp trừu tượng cha cho tất cả Model.
 * Cung cấp kết nối DB và tên bảng dùng chung, tránh lặp code.
 */
abstract class BaseModel {
    protected PDO $conn;
    protected string $table_name = '';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }
}
?>
