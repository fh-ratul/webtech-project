<?php

session_start();

define("APP_ROOT", dirname(__DIR__));

$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? "");
$appRoot = realpath(APP_ROOT);
$baseUrl = "";

if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
	$baseUrl = str_replace("\\", "/", substr($appRoot, strlen($docRoot)));
}

define("APP_BASE_URL", rtrim($baseUrl, "/"));

define("APP_ENV", "local");

require_once __DIR__ . "/constants.php";
require_once APP_ROOT . "/core/Session.php";
require_once APP_ROOT . "/core/Helpers.php";

$conn = require __DIR__ . "/database.php";