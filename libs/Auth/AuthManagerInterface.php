<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/10 0010
 * Time: 17:11
 */

namespace ToolkitPlus\Auth;

/**
 * Interface AuthManagerInterface
 * @package ToolkitPlus\Auth
 */
interface AuthManagerInterface
{
    /**
     * do login
     * @param IdentityInterface $identity
     * @return bool
     */
    public function login(IdentityInterface $identity): bool;

    /**
     * check current user is logged
     * @return bool
     */
    public function isLogin(): bool;

    /**
     * @return bool
     */
    public function isGuest(): bool;

    /**
     * @param bool|false $force
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function refreshIdentity($force = false);
}
