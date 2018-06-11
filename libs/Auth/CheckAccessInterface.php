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
     * @param int|string|mixed $userId more please see PermManager::canAccess
     * @param string $permission permission name OR uri path
     * @param array $params
     * @return bool
     */
    public function checkAccess($userId, $permission, array $params = []): bool ;
}
