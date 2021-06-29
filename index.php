<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

use ApiGenerator\Generator;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

try {
    if (!empty($config)) {
        $configuration = new Configuration();
        $conn = DriverManager::getConnection($config['database'], $configuration);
    }
} catch (Exception $e) {
    throw new Exception('Connection to database is down!');
}

$uri = $_SERVER['PATH_INFO'];
$parts = explode('/', $uri);
if ($parts[1] !== 'api') {
    throw new Error('The path is not a valid api request!');
}
$module = $parts[2] ?? null;
$id = $parts[3] ?? null;
$params = $_REQUEST;
if (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH', 'POST'])) {
    $request_body = file_get_contents('php://input');
    $params = (array)json_decode($request_body);
}

if (isset($conn)) {
    $generator = new Generator($conn);
    $generator->api($module, $id, $params);
}

