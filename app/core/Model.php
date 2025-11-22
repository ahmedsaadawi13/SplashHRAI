<?php
// FILE: /app/core/Model.php

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find($id, $tenantId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = array($id);

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        return $this->db->fetch($sql, $params);
    }

    public function findAll($tenantId = null, $conditions = array(), $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = array();
        $where = array();

        if ($tenantId !== null) {
            $where[] = "tenant_id = ?";
            $params[] = $tenantId;
        }

        foreach ($conditions as $field => $value) {
            $where[] = "{$field} = ?";
            $params[] = $value;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, array_values($data));
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $tenantId = null) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = array();
        $params = array();

        foreach ($data as $field => $value) {
            $fields[] = "{$field} = ?";
            $params[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) .
               " WHERE {$this->primaryKey} = ?";
        $params[] = $id;

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        return $this->db->query($sql, $params);
    }

    public function delete($id, $tenantId = null) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = array($id);

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        return $this->db->query($sql, $params);
    }

    public function count($tenantId = null, $conditions = array()) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = array();
        $where = array();

        if ($tenantId !== null) {
            $where[] = "tenant_id = ?";
            $params[] = $tenantId;
        }

        foreach ($conditions as $field => $value) {
            $where[] = "{$field} = ?";
            $params[] = $value;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $result = $this->db->fetch($sql, $params);
        return (int)$result['count'];
    }
}
