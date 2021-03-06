<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/1
 * Time: 下午4:51
 */

namespace ToolkitPlus\Auth;

/**
 * Class AccessChecker - access checker example
 * @package ToolkitPlus\Auth
 */
class AccessChecker implements CheckAccessInterface
{
    /**
     * @param int|string|mixed $userId
     * @param string $permission permission name OR uri path
     * @param array $params
     * @return bool
     */
    public function checkAccess($userId, $permission, array $params = []): bool
    {
        return true;
    }
}
