<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/1
 * Time: 下午4:53
 */

namespace Inhere\LibraryPlus\Auth;

/**
 * Interface CheckAccessInterface
 * @package Inhere\LibraryPlus\Auth
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
