<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/controllers/AuthController.php";

$authController = new AuthController($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$authController->login();
} else {
	$authController->showLogin();
}