<?php

require __DIR__ . "/config/config.php";
require __DIR__ . "/core/Router.php";

$router = new Router();

require __DIR__ . "/routes.php";

$router->dispatch();
