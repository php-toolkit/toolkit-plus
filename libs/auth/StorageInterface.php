<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/7/29
 * Time: 上午11:08
 */

namespace inhere\libraryPlus\auth;

/**
 * Interface StorageInterface
 * @package inhere\libraryPlus\auth
 */
interface StorageInterface
{
    public function get($key, $default = null);
    public function set($key, $value);
}
