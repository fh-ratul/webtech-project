<?php

class Session
{
    public static function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $baseUrl = rtrim(APP_BASE_URL, "/");
            header("Location: {$baseUrl}/auth/login");
            exit();
        }
    }

    public static function requireRole(string $role): void
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
            die("Access Denied");
        }
    }
}
