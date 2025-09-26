<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        $id = (int)$id;
        $conn = $this->getConnection();
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ? [$row] : [];
    }

    public function findUser($keyword) {
        $conn = $this->getConnection();
        $kw = '%' . $keyword . '%';
        $sql = "SELECT * FROM users WHERE name LIKE ? OR fullname LIKE ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ss', $kw, $kw);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }

    /**
     * Authentication user
     * Backwards-compatible: if DB used md5 earlier, verify and rehash on success.
     */
    public function auth($userName, $password) {
        $conn = $this->getConnection();
        $sql = "SELECT * FROM users WHERE name = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('s', $userName);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if (!$user) return null;

        $stored = $user['password'] ?? '';

        // First try password_verify (modern hashes)
        if (password_verify($password, $stored)) {
            // success
            return [$user];
        }

        // Fallback: if stored looks like md5 (32 hex) try md5 check, then rehash
        if (preg_match('/^[a-f0-9]{32}$/i', $stored) && md5($password) === $stored) {
            // rehash to modern algorithm
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update) {
                $uid = (int)$user['id'];
                $update->bind_param('si', $newHash, $uid);
                $update->execute();
                $update->close();
            }
            // return user (with old data)
            return [$user];
        }

        return null;
    }

    /**
     * Delete user by id
     */
    public function deleteUserById($id) {
        $id = (int)$id;
        $conn = $this->getConnection();
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Update user
     */
    public function updateUser($input) {
        $conn = $this->getConnection();
        $id = (int)($input['id'] ?? 0);
        $name = $input['name'] ?? '';
        $fullname = $input['fullname'] ?? '';
        $type = $input['type'] ?? 'user';

        if (!empty($input['password'])) {
            $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name = ?, password = ?, fullname = ?, type = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('ssssi', $name, $passwordHash, $fullname, $type, $id);
        } else {
            $sql = "UPDATE users SET name = ?, fullname = ?, type = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('sssi', $name, $fullname, $type, $id);
        }
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Insert user
     */
    public function insertUser($input) {
        $conn = $this->getConnection();
        $name = $input['name'] ?? '';
        $passwordHash = password_hash($input['password'] ?? '', PASSWORD_DEFAULT);
        $fullname = $input['fullname'] ?? '';
        $type = $input['type'] ?? 'user';

        $sql = "INSERT INTO users (name, password, fullname, type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssss', $name, $passwordHash, $fullname, $type);
        $res = $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        return $res ? $insertId : false;
    }

    /**
     * Search users / getUsers
     */
    public function getUsers($params = []) {
        $conn = $this->getConnection();

        if (!empty($params['keyword'])) {
            // safe LIKE search using prepared statement
            $kw = '%' . $params['keyword'] . '%';
            $sql = "SELECT * FROM users WHERE name LIKE ? OR fullname LIKE ? ORDER BY id DESC";
            $stmt = $conn->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param('ss', $kw, $kw);
            $stmt->execute();
            $res = $stmt->get_result();
            $rows = [];
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            $stmt->close();
            return $rows;
        } else {
            $sql = 'SELECT * FROM users ORDER BY id DESC';
            return $this->select($sql);
        }
    }
}
