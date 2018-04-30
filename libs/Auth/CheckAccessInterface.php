<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/1
 * Time: 下午4:53
 */

namespace ToolkitPlus\Auth;

/**
 * Interface CheckAccessInterface
 * @package ToolkitPlus\Auth
 */
interface CheckAccessInterface
{
    /**
     * @param $userId
     * @param $permission
     * @param array $params
     * @return bool
     */
    public function checkAccess($userId, $permission, $params = []);
}
