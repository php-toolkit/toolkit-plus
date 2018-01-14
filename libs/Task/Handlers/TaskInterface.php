<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/5/29
 * Time: 上午10:25
 */

namespace Inhere\LibraryPlus\Task\Handlers;

use Inhere\LibraryPlus\Task\Server\TaskWrapper;

/**
 * Interface TaskInterface
 * @package Inhere\LibraryPlus\Task\Handlers
 */
interface TaskInterface
{
    /**
     * do the task
     * @param string $workload
     * @param TaskWrapper $task
     * @return mixed
     */
    public function run($workload, TaskWrapper $task);
}
