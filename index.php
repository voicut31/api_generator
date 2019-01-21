<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

use ApiGenerator\Generator;

$configuration = new \Doctrine\DBAL\Configuration();

$conn = \Doctrine\DBAL\DriverManager::getConnection($config['database'], $configuration);

$generator = new Generator($conn);
$generator->generate();
