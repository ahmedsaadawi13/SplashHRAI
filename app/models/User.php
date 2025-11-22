<?php
// FILE: /app/models/User.php

class User extends Model {
    protected $table = 'users';

    public function findByEmail($email, $tenantId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $params = array($email);

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        return $this->db->fetch($sql, $params);
    }

    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && Security::verifyPassword($password, $user['password'])) {
            // Update last login
            $this->db->query(
                "UPDATE {$this->table} SET last_login_at = ? WHERE id = ?",
                array(date('Y-m-d H:i:s'), $user['id'])
            );
            return $user;
        }

        return false;
    }

    public function updatePassword($userId, $newPassword) {
        $hashedPassword = Security::hashPassword($newPassword);
        return $this->update($userId, array('password' => $hashedPassword));
    }
}
