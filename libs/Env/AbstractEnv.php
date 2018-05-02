<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2015/2/27
 * Use : environment information
 * File: AbstructEnv.php
 */

namespace ToolkitPlus\Env;

use Toolkit\Collection\SimpleCollection;

/**
 * Class AbstractEnv
 * @package ToolkitPlus\Env
 */
abstract class AbstractEnv extends SimpleCollection
{
    /**
     * @var array
     */
    static public $config = [];

    /**
     * 初始化信息
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        foreach (static::$config as $name => $realName) {
            $this->set($name, isset($_SERVER[$realName]) ? trim($_SERVER[$realName]) : null);
        }

        $this->init();
    }

    public function init()
    {
    }
}
