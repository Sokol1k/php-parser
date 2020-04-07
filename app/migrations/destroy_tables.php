<?php

namespace Migration;

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '../../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '../../')->load();

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => getenv('MYSQL_DRIVER'),
    'host'      => getenv('MYSQL_HOST'),
    'database'  => getenv('MYSQL_DATABASE'),
    'username'  => getenv('MYSQL_USERNAME'),
    'password'  => getenv('MYSQL_PASSWORD'),
    'charset'   => getenv('MYSQL_CHARSET'),
    'collation' => getenv('MYSQL_COLLACTION'),
    'prefix'    => getenv('MYSQL_PREFIX'),
]);

$capsule->setAsGlobal();

$capsule->schema()->dropIfExists('question_answer');
echo "Deleted table \"question_answer\"\n"; 

$capsule->schema()->dropIfExists('questions');
echo "Deleted table \"questions\"\n"; 

$capsule->schema()->dropIfExists('answers');
echo "Deleted table \"answers\"\n"; 
