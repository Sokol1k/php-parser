<?php

namespace Core;

use Core\Log;
use Exception;
use Predis\Client;

class Redis
{

    private $DB;
    private $logger;

    /**
     * Constructor for creating of Redis.
     * 
     * @return  void
     * 
     */
    public function __construct()
    {
        try {

            $this->logger = Log::getInstance("Parser");

            $this->DB = new Client([
                'scheme' => getenv('REDIS_SCHEME'),
                'host'   => getenv('REDIS_HOST'),
                'port'   => getenv('REDIS_PORT')
            ]);

            $this->logger->debug("Redis: Connected to database");
        } catch (Exception $e) {

            $this->logger->debug("Redis: Failed to connect to Redis database: " . $e->getMessage());
        }
    }

    /**
     * Receives data and write in database.
     * 
     * @param   string  $key
     * @param   string|array  $value
     * @return  void
     * 
     */
    public function set(String $key, $value)
    {
        try {

            $this->DB->sadd($key, $value);
        } catch (Exception $e) {

            $this->logger->debug("Redis: Failed to add data to redis database: " . $e->getMessage());
        }
    }

    /**
     * Returns one data by key.
     * 
     * @param   string  $key
     * @return  string
     * 
     */
    public function get(String $key)
    {
        try {

            return $this->DB->spop($key);
        } catch (Exception $e) {

            $this->logger->debug("Redis: Failed to get data from redis database: " . $e->getMessage());
        }
    }

    /**
     * Returns amount data by key.
     * 
     * @param   string  $key
     * @return  int
     * 
     */
    public function amount(String $key)
    {
        try {

            return $this->DB->scard($key);
        } catch (Exception $e) {

            $this->logger->debug("Redis: Failed to get amount of data from redis database: " . $e->getMessage());
        }
    }

    /**
     * Checks if there is an entry in the database or not.
     * 
     * @param   string  $key
     * @param   string  $value
     * @return  int
     */
    public function check($key, $value)
    {
        try {

            return $this->DB->sismember($key, $value);
        } catch (Exception $e) {
            
            $this->logger->debug("Redis: Failed to check value in Redis database: " . $e->getMessage());
        }
    }
}
