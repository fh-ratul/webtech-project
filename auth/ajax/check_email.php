<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../models/User.php";

header("Content-Type: application/json");

$email = trim($_GET['email'] ?? "");
$userModel = new User($conn);

echo json_encode([
    "exists" => $email !== "" ? $userModel->emailExists($email) : false
]);
