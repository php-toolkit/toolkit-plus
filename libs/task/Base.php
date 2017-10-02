<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/5/21
 * Time: 上午1:52
 */

namespace Inhere\LibraryPlus\Task;

use Inhere\Library\Helpers\CliHelper;
use Inhere\Library\process\ProcessLogger;
use Inhere\Library\queue\QueueInterface;
use Inhere\Library\Traits\ConfigTrait;

/**
 * Class Base
 * @package Inhere\LibraryPlus\Task
 */
abstract class Base
{
    use ConfigTrait;
    use ProcessControlTrait;

    const VERSION = '0.1.0';

    /**
     * @var int
     */
    protected $pid = 0;

    /**
     * @var ProcessLogger
     */
    protected $lgr;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * run
     */
    abstract public function run();

    /**
     * Install Signals
     */
    abstract public function installSignals();

    /**
     * Handle signals
     * @param int $sigNo
     */
    abstract public function signalHandler($sigNo);

    /**
     * @return string
     */
    // abstract public function getPidRole();

    /**
     * Logs data to disk or stdout
     * @param string $msg
     * @param int $level
     * @param array $data
     * @return bool
     */
    public function log($msg, $level = ProcessLogger::INFO, array $data = [])
    {
        $msg = sprintf('[%s:%d] %s', $this->getPidRole(), $this->pid, $msg);

        return $this->lgr->log($msg, $level, $data);
    }

    /**
     * Logs data to stdout
     * @param string $text
     * @param bool $nl
     * @param bool|int $quit
     */
    protected function stdout($text, $nl = true, $quit = false)
    {
        CliHelper::stdout($text, $nl, $quit);
    }

    /**
     * Logs data to stderr
     * @param string $text
     * @param bool $nl
     * @param bool|int $quit
     */
    protected function stderr($text, $nl = true, $quit = -200)
    {
        CliHelper::stderr($text, $nl, $quit);
    }

    /**
     * @return QueueInterface
     */
    public function getQueue(): QueueInterface
    {
        return $this->queue;
    }

    /**
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getShowName()
    {
        return $this->name ? "({$this->name})" : '';
    }

    /**
     * @return bool
     */
    public function isDaemon()
    {
        return $this->config['daemon'];
    }
}
