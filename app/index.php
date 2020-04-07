<?php

use Core\Parser;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->load();

// $parse = new Parser("https://www.wort-suchen.de/", '/kreuzwortraetsel-hilfe/loesungen/auto/');

// $parse = new Parser("https://www.wort-suchen.de/", '/kreuzwortraetsel-hilfe/loesungen/apfel/');

// $parse = new Parser("https://www.wort-suchen.de/", '/kreuzwortraetsel-hilfe/loesungen/frage/');

$parse = new Parser("https://www.wort-suchen.de/", '/kreuzwortraetsel-hilfe/loesungen/hilfe/');

$parse->start();
