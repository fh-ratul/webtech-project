<?php

require_once __DIR__ . "/constants.php";
require_once BASE_PATH . "/core/Database.php";

$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
return $db->getConnection();
