<?php

require_once __DIR__ . "/../../config/config.php";

header("Content-Type: application/json");

$email = trim($_POST['email'] ?? "");
$password = trim($_POST['password'] ?? "");

if ($email === "" || $password === "") {
    echo json_encode(["ok" => false, "message" => "Email and password are required."]);
    exit();
}

echo json_encode(["ok" => true]);
