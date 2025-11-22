<?php
// FILE: /app/helpers/ActivityLog.php

class ActivityLog {

    public static function log($tenantId, $userId, $entityType, $entityId, $action, $description) {
        $db = Database::getInstance();

        $data = array(
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'ip_address' => Security::getClientIp(),
            'user_agent' => Security::getUserAgent(),
            'created_at' => date('Y-m-d H:i:s')
        );

        $sql = "INSERT INTO activity_logs (tenant_id, user_id, entity_type, entity_id, action, description, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $db->query($sql, array_values($data));
    }

    public static function getRecent($tenantId, $limit = 50) {
        $db = Database::getInstance();

        $sql = "SELECT al.*, u.first_name, u.last_name
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tenant_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";

        return $db->fetchAll($sql, array($tenantId, $limit));
    }

    public static function getByEntity($tenantId, $entityType, $entityId) {
        $db = Database::getInstance();

        $sql = "SELECT al.*, u.first_name, u.last_name
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tenant_id = ? AND al.entity_type = ? AND al.entity_id = ?
                ORDER BY al.created_at DESC";

        return $db->fetchAll($sql, array($tenantId, $entityType, $entityId));
    }
}
