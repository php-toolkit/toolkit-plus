<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-20
 * Time: 15:49
 */

namespace ToolkitPlus\Log;


/**
 * Interface HandlerInterface
 * @package ToolkitPlus\Log
 */
interface HandlerInterface
{
    public function isHandling(array $record);

    public function handle(array $logs, $final);
}
