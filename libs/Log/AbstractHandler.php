<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-20
 * Time: 16:46
 */

namespace ToolkitPlus\Log;

use Psr\Log\LogLevel;

/**
 * Class AbstractHandler
 * @package ToolkitPlus\Log
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var boolean whether to enable this log handler. Defaults to true.
     */
    public $enabled = true;

    /**
     * only want to exported categories
     * @var array
     */
    public $categories = [];

    /**
     * the excepted categories, them are will not export.
     * @var array
     */
    public $except = [];

    /**
     * @var array
     */
    public $logs = [];

    /**
     * @var int
     */
    public $exportInterval = 1000;

    /**
     * @var int
     */
    private $level = Logger::DEBUG;

    /** @var bool */
    protected $stop = false;

    /**
     * @var \Closure
     */
    private $contextCollector;

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $record['level'] >= $this->level;
    }

    /**
     * @param array $logs
     * @param $final
     */
    public function handle(array $logs, $final)
    {
        $this->logs = array_merge($this->logs, static::filterLogs($logs, $this->getLevel(), $this->categories, $this->except));
        $count = \count($this->logs);

        if ($count > 0 && ($final || ($this->exportInterval > 0 && $count >= $this->exportInterval))) {
            if ($collector = $this->contextCollector) {
                $this->logs[] = [$collector($this), LogLevel::INFO, 'application', microtime(true)];
            }

            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->logs = [];
        }
    }

    /**
     * Writes the record down to the log of the implementing handler
     * @param  array $record
     * @return void
     */
    abstract protected function write(array $record);

    abstract protected function export();

    /**
     * @return string
     */
    protected function getContextLog(): string
    {
        return '';
    }

    /**
     * @param array $logs
     * @param int $levels
     * @param array $categories
     * @param array $except except category
     * @return array
     */
    public static function filterLogs(array $logs, $levels = 0, array $categories = [], array $except = []): array
    {
        foreach ($logs as $i => $message) {
            if ($levels && !($levels & $message[1])) {
                unset($logs[$i]);
                continue;
            }

            $matched = empty($categories);
            foreach ($categories as $category) {
                if (
                    $message[2] === $category ||
                    (!empty($category) && substr_compare($category, '*', -1, 1) === 0 && strpos($message[2], rtrim($category, '*')) === 0)
                ) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($except as $category) {
                    $prefix = rtrim($category, '*');
                    if (($message[2] === $category || $prefix !== $category) && strpos($message[2], $prefix) === 0) {
                        $matched = false;
                        break;
                    }
                }
            }

            if (!$matched) {
                unset($logs[$i]);
            }
        }

        return $logs;
    }

    /**
     * Sets minimum logging level at which this handler will be triggered.
     * @param  int|string $level Level or level name
     * @return self
     */
    public function setLevel($level): self
    {
        $this->level = Logger::toNumberLevel($level);

        return $this;
    }

    /**
     * Gets minimum logging level at which this handler will be triggered.
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
