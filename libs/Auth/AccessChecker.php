<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/1
 * Time: 下午4:51
 */

namespace ToolkitPlus\Auth;

/**
 * Class AccessChecker
 * @package ToolkitPlus\Auth
 */
class AccessChecker implements CheckAccessInterface
{
    /**
     * @param $userId
     * @param $permission
     * @param array $params
     * @return bool
     */
    public function checkAccess($userId, $permission, $params = []): bool
    {
        return true;
    }
}
