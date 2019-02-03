<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

use ApiGenerator\Generator;

$configuration = new \Doctrine\DBAL\Configuration();

$conn = \Doctrine\DBAL\DriverManager::getConnection($config['database'], $configuration);

$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', $uri);
if ($parts[1] !== 'api') {
    throw new Error('The path is not a valid api request!');
}
$module = $parts[2] ?? null;
$id = $parts[3] ?? null;
$params = $_REQUEST;

$generator = new Generator($conn);
$generator->api($module, $id, $params);
