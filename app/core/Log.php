<?php

namespace Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
    private static $logger;
    private static $_instance;

    /**
     * Constructor for creating of Log.
     * 
     * @param   string  $name
     * @return  void
     * 
     */
    private function __construct(String $name)
    {
        self::$logger = new Logger($name);

        self::$logger->pushHandler(new StreamHandler('logs/debug/' . date("d.m.Y H:i:s")));

        if (getenv('DEBUG') == 'true') {
            self::$logger->pushHandler(new StreamHandler('php://stdout'));
        }
    }

    /**
     * Constructor for creating of Log.
     * 
     * @param   string  $name
     * @return  void
     * 
     */
    public static function getInstance(String $name)
    {
        if (!self::$_instance) {
            self::$_instance = new self($name);
        }
        return self::$_instance;
    }

    /**
     * Writes message in logger.
     * 
     * @param   string  $message
     * @return  void
     * 
     */
    public static function debug(String $message)
    {
        self::$logger->debug($message);
    }
}
