<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/11 0011
 * Time: 19:59
 */

namespace ToolkitPlus\Auth;

/**
 * Class PermManager - permission manager
 * @package ToolkitPlus\Auth
 */
class PermManager implements PermManagerInterface
{
    const ALL = '*';

    /**
     * checked permission caching list
     * @var array
     * e.g.
     * [
     *  'userKey' => [
     *      'createPost' => true,
     *      'deletePost' => false,
     *  ]
     * ]
     */
    private $accesses = [];

    /**
     * @var array
     */
    private $permissions = [];

    /**
     * @var CheckAccessInterface
     */
    private $accessChecker;

    /**
     * alias of the `canAccess`
     * @param int|string|mixed $user A user info.
     * @param string $permission a permission name or a url
     * @param array $params
     * @param bool|true $caching
     * @return bool
     */
    public function userHas($user, $permission, array $params = [], $caching = true): bool
    {
        return $this->canAccess($user, $permission, $params, $caching);
    }

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
    public function canAccess($user, $permission, array $params = [], $caching = true): bool
    {
        $uniKey = $this->genKey($user);

        if (isset($this->accesses[$uniKey][$permission])) {
            return $this->accesses[$uniKey][$permission];
        }

        $access = false;

        if ($checker = $this->getAccessChecker()) {
            $access = $checker->checkAccess($user, $permission, $params);

            if ($caching) {
                $this->accesses[$uniKey][$permission] = $access;
            }
        }

        return $access;
    }

    /**
     * @param mixed $user
     * @param string $permission
     * @param bool $bool
     */
    public function assign($user, $permission, $bool = true)
    {
        $uniKey = $this->genKey($user);

        $this->accesses[$uniKey][$permission] = (bool)$bool;
    }

    /**
     * @param mixed $user
     * @param array $permissions
     */
    public function assigns($user, array $permissions)
    {
        $uniKey = $this->genKey($user);

        foreach ($permissions as $permission) {
            $this->accesses[$uniKey][$permission] = true;
        }
    }

    /**
     * @param mixed $user
     * @param string $permission
     */
    public function cancel($user, $permission)
    {
        $uniKey = $this->genKey($user);

        if ($this->accesses[$uniKey][$permission]) {
            unset($this->accesses[$uniKey][$permission]);
        }
    }

    /**
     * @param mixed $user
     * @param array $permissions
     */
    public function cancels($user, $permissions)
    {
        $uniKey = $this->genKey($user);

        foreach ($permissions as $permission) {
            $this->accesses[$uniKey][$permission] = false;
        }
    }

    /**
     * @param string $permission
     * @param bool $enable
     */
    public function addPermission(string $permission, bool $enable = true)
    {
        $this->permissions[$permission] = $enable;
    }

    /**
     * @param array $permissions
     */
    public function addPermissions(array $permissions)
    {
        $this->permissions = \array_merge($this->permissions, $permissions);
    }

    /**
     * @param string $permission
     */
    public function delPermission(string $permission)
    {
        if ($this->permissions[$permission]) {
            unset($this->permissions[$permission]);
        }
    }

    /**
     * @param mixed $user
     * @return string
     */
    protected function genKey($user): string
    {
        return \is_scalar($user) ? \md5((string)$user) : \md5(\json_encode($user));
    }

    /**
     * @return CheckAccessInterface
     */
    public function getAccessChecker(): CheckAccessInterface
    {
        return $this->accessChecker;
    }

    /**
     * @param CheckAccessInterface $accessChecker
     */
    public function setAccessChecker(CheckAccessInterface $accessChecker)
    {
        $this->accessChecker = $accessChecker;
    }

    /**
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
    }
}
