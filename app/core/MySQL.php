<?php

namespace Core;

use Core\Log;
use Exception;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

class MySQL
{
    private $DB;
    private $logger;

    /**
     * Constructor for creating of MySQL.
     * 
     * @return  void
     * 
     */
    public function __construct()
    {
        try {

            $this->logger = Log::getInstance("Parser");

            $this->DB = new Capsule;

            $this->DB->addConnection([
                'driver'    => getenv('MYSQL_DRIVER'),
                'host'      => getenv('MYSQL_HOST'),
                'database'  => getenv('MYSQL_DATABASE'),
                'username'  => getenv('MYSQL_USERNAME'),
                'password'  => getenv('MYSQL_PASSWORD'),
                'charset'   => getenv('MYSQL_CHARSET'),
                'collation' => getenv('MYSQL_COLLACTION'),
                'prefix'    => getenv('MYSQL_PREFIX'),
            ]);

            $this->DB->setEventDispatcher(new Dispatcher(new Container));

            $this->DB->setAsGlobal();
            
            $this->DB->bootEloquent();

            $this->logger->debug("MySQL: Connected to database");
        } catch (Exception $e) {

            $this->logger->debug("MySQL: Failed to connect to MySQL database: " . $e->getMessage());
        }
    }

    /**
     * Receives data and write in table "question_answer".
     * 
     * @param   array $result
     * @return  void
     * 
     */
    public function set(array $result)
    {
        if (count($result)) {
            try {

                $idQuestion = $this->createQuestion($result[0])->id;
                $idAnswer = $this->createAnswer($result[1])->id;

                $this->DB->table('question_answer')->updateOrInsert(['question_id' => $idQuestion, 'answer_id' => $idAnswer]);
            } catch (Exception $e) {

                $this->logger->debug("MySQL: Failed to add data to table \"question_answer\" of mySQL database.: " . $e->getMessage());
            }
        }
    }

    /**
     * Receives data and write in table "questions".
     * 
     * @param  string $question
     * @return Illuminate\Database\Capsule\Manager as Capsule
     * 
     */
    private function createQuestion(String $question)
    {
        try {

            $this->DB->table('questions')->updateOrInsert(['text' => $question]);

            return $this->DB->table('questions')->select('id')->where('text', $question)->first();
        } catch (Exception $e) {

            $this->logger->debug("MySQL: Failed to add data to table \"questions\" of mySQL database.: " . $e->getMessage());
        }
    }

    /**
     * Receives data and write in table "answers".
     * 
     * @param  string $answer
     * @return Illuminate\Database\Capsule\Manager as Capsule
     * 
     */
    private function createAnswer(String $answer)
    {
        try {

            $this->DB->table('answers')->updateOrInsert(['text' => $answer]);

            return $this->DB->table('answers')->select('id')->where('text', $answer)->first();
        } catch (Exception $e) {

            $this->logger->debug("MySQL: Failed to add data to table \"answers\" of mySQL database.: " . $e->getMessage());
        }
    }
}
