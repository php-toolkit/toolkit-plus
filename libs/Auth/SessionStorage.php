<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/10 0010
 * Time: 16:06
 */

namespace ToolkitPlus\Auth;

/**
 * Class SessionStorage
 * @package ToolkitPlus\Auth
 */
class SessionStorage implements StorageInterface
{
    /**
     * SessionStorage constructor.
     * @throws \RuntimeException
     */
    public function __construct()
    {
        if (!\session_id()) {
            throw new \RuntimeException('Must start session if you want use session storage.');
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return null
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        unset($_SESSION[$key]);
        return true;
    }
}
