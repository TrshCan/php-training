<?php
// models/UserModel.php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    /**
     * Find user by ID (safe with prepared statements)
     */
    public function findUserById($id): array
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Find user by keyword in name or email (safe)
     */
    public function findUser(string $keyword): array
    {
        $sql = "SELECT * FROM users WHERE name LIKE ? OR email LIKE ?";
        $stmt = $this->getConnection()->prepare($sql);
        $like = "%" . $keyword . "%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Authenticate user by username & password
     */
    public function auth(string $userName, string $password): array
    {
        $md5Password = md5($password); // legacy, not secure
        $sql = "SELECT * FROM users WHERE name = ? AND password = ?";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("ss", $userName, $md5Password);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Delete user by id (safe)
     */
    public function deleteUserById(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Update user (safe)
     */
    public function updateUser(array $input): bool
    {
        $sql = "UPDATE users SET name = ?, password = ? WHERE id = ?";
        $stmt = $this->getConnection()->prepare($sql);

        $hashed = md5($input['password']);
        $stmt->bind_param("ssi", $input['name'], $hashed, $input['id']);

        return $stmt->execute();
    }

    /**
     * Insert user (safe)
     */
    public function insertUser(array $input): int
    {
        $sql = "INSERT INTO users (name, password) VALUES (?, ?)";
        $stmt = $this->getConnection()->prepare($sql);

        $hashed = md5($input['password']);
        $stmt->bind_param("ss", $input['name'], $hashed);
        $stmt->execute();

        return $this->getConnection()->insert_id;
    }

    /**
     * Search users (this one intentionally left vulnerable if you need SQLi test)
     * DO NOT use in production.
     */
    public function getUsers(array $params = []): array
    {
        if (!empty($params['keyword'])) {
            $sql = 'SELECT * FROM users WHERE name LIKE "%' . $params['keyword'] . '%"';
            // ⚠ unsafe: left for demonstration of SQL injection
            $users = $this->query($sql);
            $rows = [];
            if ($users instanceof mysqli_result) {
                while ($row = $users->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
            return $rows;
        } else {
            $sql = "SELECT * FROM users";
            return $this->select($sql);
        }
    }
}
