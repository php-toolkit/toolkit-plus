<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/7/29
 * Time: 上午11:08
 */

namespace Inhere\LibraryPlus\Auth;

/**
 * Interface StorageInterface
 * @package Inhere\LibraryPlus\Auth
 */
interface StorageInterface
{
    public function get($key, $default = null);

    public function set($key, $value);
}
