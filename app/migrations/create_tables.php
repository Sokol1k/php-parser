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

$capsule->schema()->create('answers', function ($table) {
    $table->increments('id');
    $table->string('text', 255)->unique()->nullable(false);

    echo "Created table \"answers\"\n"; 
});

$capsule->schema()->create('questions', function ($table) {
    $table->increments('id');
    $table->string('text', 255)->unique()->nullable(false);

    echo "Created table \"questions\"\n";
});

$capsule->schema()->create('question_answer', function ($table) {
    $table->integer('question_id')->unsigned()->nullable(false);
    $table->integer('answer_id')->unsigned()->nullable(false);

    $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('answer_id')->references('id')->on('answers')->onDelete('cascade')->onUpdate('cascade');
    
    echo "Created table \"question_answer\"\n";
});
