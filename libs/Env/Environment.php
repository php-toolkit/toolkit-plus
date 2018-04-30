<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2015/2/27
 * Use : ...
 * File: Environment.php
 */

namespace ToolkitPlus\Env;

use Inhere\Library\Collections\SimpleCollection;

/**
 * 环境信息
 * Class Environment
 * @package ToolkitPlus\Env
 */
class Environment extends SimpleCollection
{

    /**
     * Create mock environment
     * @param  array $userData Array of custom environment keys and values
     * @return self
     */
    public static function mock(array $userData = [])
    {
        $data = array_merge([
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '',
            'REQUEST_URI' => '',
            'QUERY_STRING' => '',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'localhost',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'HTTP_USER_AGENT' => 'Slim Framework',
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
        ], $userData);

        return new static($data);
    }

}// end class Environment
