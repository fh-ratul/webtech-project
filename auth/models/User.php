<?php

class User
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function findByEmail(string $email): ?array
    {
        $query = "SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    public function findById(int $id): ?array
    {
        $query = "SELECT id, name, email, password_hash, role, is_active FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    public function emailExists(string $email): bool
    {
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    public function createStudent(string $name, string $email, string $hash, string $studentId, string $program): bool
    {
        $query = "
        INSERT INTO users
        (name, email, password_hash, role, student_id, program, is_active)
        VALUES
        (?, ?, ?, 'student', ?, ?, 1)
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssss", $name, $email, $hash, $studentId, $program);

        return $stmt->execute();
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $query = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $hash, $id);

        return $stmt->execute();
    }
}
