<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/7/29
 * Time: 上午11:08
 */

namespace ToolkitPlus\Auth;

/**
 * Interface StorageInterface
 * @package ToolkitPlus\Auth
 */
interface StorageInterface
{
    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function set(string $key, $value);

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool ;
}
