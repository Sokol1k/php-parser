<?php

namespace Core;

use Core\Log;
use Exception;
use Core\Redis;
use Core\MySQL;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    private $url;
    private $host;
    private $logger;
    private $redisDB;
    private $threads;
    private $mySqlDB;
    private $quantityStreams;

    /**
     * Constructor for creating of parser.
     * 
     * @param   string  $host
     * @param   string  $url
     * @return  void
     * 
     */
    public function __construct(String $host, String $url)
    {
        $this->url = $url;
        $this->host = $host;
        $this->threads = [];
        $this->redisDB = new Redis();
        $this->mySqlDB = new MySQL();
        $this->quantityStreams = getenv('QUANTITY_STREAMS');
        $this->logger = Log::getInstance("Parser");

        pcntl_signal(SIGINT, [$this, 'sigHandler']);
        pcntl_signal(SIGTERM, [$this, 'sigHandler']);
    }

    /**
     * Launches the parser.
     * 
     * @return  void
     * 
     */
    public function start()
    {
        echo "Start" . PHP_EOL;
        $this->logger->debug("Parser: Start");

        $this->checkUrl($this->url);

        while (true) {

            if ($this->hasStream()) {

                break;
            } elseif ($this->isNotFullStreams() && $this->hasDataLinks()) {

                $link = $this->redisDB->get('links');

                $this->run($link);
            } else {

                foreach ($this->threads as $pid => $link) {

                    $this->wait($pid);
                }
            }
        }

        $this->logger->debug("Parser: Finish");
        echo "Finish" . PHP_EOL;
    }

    /**
     * Function for signal processing.
     * 
     * @param   int  $signal
     * @return  void
     * 
     */
    private function sigHandler(int $signal)
    {
        switch ($signal) {
            case SIGINT:
            case SIGTERM: {

                    $pid = getmypid();

                    $this->redisDB->set('links', $this->threads[$pid]);

                    unset($this->threads[$pid]);

                    break;
                }
        }
    }

    /**
     * Waiting for the end of the forks.
     * 
     * @param   int  $pid
     * @return  void
     * 
     */
    private function wait(int $pid)
    {

        $status = 0;

        pcntl_waitpid($pid, $status, WUNTRACED);

        if (pcntl_wifexited($status)) {

            unset($this->threads[$pid]);
        }
    }

    /**
     * Launches multithreading and the parser.
     * 
     * @param   string $link
     * @return  void
     * 
     */
    private function run(String $link)
    {

        if ($link) {

            $this->redisDB = new Redis();

            $pid = pcntl_fork();

            if ($pid == -1) {

                $this->logger->debug("Parser: Can\'t fork process");

                die("Can\'t fork process");
            } elseif ($pid) {

                $this->logger->debug("Parser: Main process have created subprocess " . $pid);

                $this->threads[$pid] = $link;
            } else {

                $pid = getmypid();

                $this->logger->debug("Parser: Forked process with pid " . $pid);

                $this->parseQuestionAnswer($link);

                $this->logger->debug("Parser: Forked is already done " . $pid);

                die();
            }
        }
    }

    /**
     * Parse questions and answers.
     * 
     * @param   string  $link
     * @return  void
     * 
     */
    private function parseQuestionAnswer(String $link)
    {
        if ($link) {

            $crawler = $this->getCrawler($link);

            $crawler
                ->filter('table.cw-results tbody tr')
                ->each(function ($node) {

                    $question = $this->parseQuestion($node);
                    $answer = $this->parseAnswer($node);

                    $result = array_merge($question, $answer);

                    $this->mySqlDB->set($result);
                });

            $crawler->filter('div.list-columns a')
                ->each(function ($node) {

                    $this->checkUrl($node->attr('href'));
                });

            $this->redisDB->set('old', $link);
        }
    }

    /**
     * Is there any data for the parser.
     * 
     * @return  bool
     * 
     */
    private function hasStream()
    {
        return (!$this->redisDB->amount('links') and !count($this->threads));
    }

    /**
     * An array of threads is full or not.
     * 
     * @return  bool
     * 
     */
    private function isNotFullStreams()
    {
        return (count($this->threads) < $this->quantityStreams);
    }

    /**
     * Are there any entries in the database for the parser.
     * 
     * @return  bool
     * 
     */
    private function hasDataLinks()
    {
        return $this->redisDB->amount('links');
    }

    /**
     * Checks if the link was parsed.
     * 
     * @param   string  $url
     * @return  void
     * 
     */
    private function checkUrl($url)
    {
        if (!$this->redisDB->check('old', $url)) {

            $this->redisDB->set('links', $url);
        }
    }

    /**
     * Parse questions.
     * 
     * @param   Symfony\Component\DomCrawler\Crawler  $node
     * @return  array
     * 
     */
    private function parseQuestion(Crawler $node)
    {
        return $node->filter('td.cw-clue a, td.cw-clue span')
            ->each(function ($elem) {

                return $elem->text();
            });
    }

    /**
     * Parse answers.
     * 
     * @param   Symfony\Component\DomCrawler\Crawler  $node
     * @return  array
     * 
     */
    private function parseAnswer(Crawler $node)
    {
        return $node->filter('td.cw-answer a, td.cw-answer span')
            ->each(function ($elem) {

                return $elem->text();
            });
    }

    /**
     * Returns crawler.
     * 
     * @param   string  $url
     * @return  Symfony\Component\DomCrawler\Crawler
     * 
     */
    private function getCrawler(String $url)
    {
        try {

            return new Crawler($this->getHtml($url));
        } catch (Exception $e) {

            $this->logger->debug("Parser: Cannot crawl page: " . $e->getMessage());
        }
    }

    /**
     * Returns HTML.
     * 
     * @param   string $url
     * @return  string
     * 
     */
    private function getHtml(String $url)
    {
        try {

            $client = new Client();
            $response = $client->request("GET", $this->normalizeUrl($url));

            return strval($response->getBody());
        } catch (Exception $e) {

            $this->logger->debug("Parser: Page not found: " . $e->getMessage());
        }
    }

    /**
     * Normalize URL.
     * 
     * @param   string  $url
     * @return  string
     * 
     */
    private function normalizeUrl(String $url)
    {
        return $this->host . ltrim($url, '/');
    }
}
