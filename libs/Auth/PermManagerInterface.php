<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/11 0011
 * Time: 23:06
 */

namespace ToolkitPlus\Auth;

/**
 * Interface PermManagerInterface
 * @package ToolkitPlus\Auth
 */
interface PermManagerInterface
{
    /**
     * check user whether can access permission
     * @param int|string|mixed $user A user info.
     * Maybe:
     * - int        user ID(is recommended)
     * - string     user token
     * - array      user info
     * - object     user object
     * @param int|string $permission
     * Maybe:
     * - int        a permission ID
     * - string     a uri path string OR a permission name
     * @param array $params
     * @param bool $caching
     * @return bool
     */
    public function canAccess($user, $permission, array $params = [], $caching = true): bool;

    /**
     * @return CheckAccessInterface
     */
    public function getAccessChecker(): CheckAccessInterface;

    /**
     * @param CheckAccessInterface $accessChecker
     */
    public function setAccessChecker(CheckAccessInterface $accessChecker);
}
