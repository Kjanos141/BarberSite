<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

$router = require APP_PATH . '/routes.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $uri);
