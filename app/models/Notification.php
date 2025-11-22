<?php
// FILE: /app/models/Notification.php

class Notification extends Model {
    protected $table = 'notifications';

    public static function create_notification($tenantId, $userId, $type, $title, $message, $link = null) {
        $db = Database::getInstance();

        $data = array(
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        );

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO notifications (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $db->query($sql, array_values($data));
    }

    public function getUnread($userId, $tenantId, $limit = 20) {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = ? AND tenant_id = ? AND is_read = 0
                ORDER BY created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, array($userId, $tenantId, $limit));
    }

    public function markAsRead($id, $userId, $tenantId) {
        $sql = "UPDATE {$this->table}
                SET is_read = 1, read_at = ?
                WHERE id = ? AND user_id = ? AND tenant_id = ?";

        return $this->db->query($sql, array(date('Y-m-d H:i:s'), $id, $userId, $tenantId));
    }

    public function markAllAsRead($userId, $tenantId) {
        $sql = "UPDATE {$this->table}
                SET is_read = 1, read_at = ?
                WHERE user_id = ? AND tenant_id = ? AND is_read = 0";

        return $this->db->query($sql, array(date('Y-m-d H:i:s'), $userId, $tenantId));
    }
}
