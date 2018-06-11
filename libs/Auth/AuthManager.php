<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/6 0006
 * Time: 21:57
 */

namespace ToolkitPlus\Auth;

use Toolkit\ObjUtil\Obj;

/**
 * Class AuthManager
 * @package ToolkitPlus\Auth
 * @property int id
 */
class AuthManager implements AuthManagerInterface
{
    /**
     * @var string
     */
    public $idColumn = 'id';

    /**
     * @var string
     */
    public $loginUrl = '/login';

    /**
     * @var string
     */
    public $loggedTo = '/';

    /**
     * @var string
     */
    public $guestTo = '/';

    /**
     * @var string
     */
    public $logoutUrl = '/logout';

    /**
     * @var string
     */
    public $logoutTo = '/';

    /**
     * the identity [model] class name
     * @var string
     */
    public $identityClass;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     * true   Auto load logged user data from storage.
     * false  You need manual load user data on before the first use
     */
    private $autoload = true;

    /**
     * @var string The session key
     */
    private $sessKey = '_user_auth_data';

    /**
     * user data persistent storage driver Bridge
     * @var StorageInterface
     */
    private $_storage;

    /**
     * checked permission caching list
     * @var array
     * e.g.
     * [
     *  'createPost' => true,
     *  'deletePost' => false,
     * ]
     */
    private $_accesses = [];

    /**
     * @var CheckAccessInterface
     */
    private $accessChecker;

    /**
     * Exclude fields that don't need to be saved.
     * @var array
     */
    protected $excepted = ['password'];

    /**
     * don't allow set attribute
     * @param array $options
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        Obj::init($this, $options);

        if ($this->identityClass === null) {
            throw new \InvalidArgumentException('The property "identityClass" must be set');
        }

        // if enable autoload and user have already login, reload user info from storage
        if ($this->autoload) {
            $this->getStorage();
            $this->refreshIdentity();
        }
    }

    /**
     * @param IdentityInterface $user
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function login(IdentityInterface $user): bool
    {
        $this->clear();
        $this->setIdentity($user);

        return $this->isLogin();
    }

    /**
     * logout
     */
    public function logout()
    {
        $this->clear();

        $this->_storage->del($this->sessKey);
    }

    /**
     * alias of the canAccess()
     * @param string $permission a permission name or a url
     * @param array $params
     * @param bool|true $caching
     * @return bool
     */
    public function can($permission, array $params = [], $caching = true): bool
    {
        return $this->canAccess($permission, $params, $caching);
    }

    /**
     * check current user can access the permission
     * @see PermManager::canAccess()
     * @param string $permission
     * @param array $params
     * @param bool $caching
     * @return bool|mixed
     */
    public function canAccess($permission, array $params = [], $caching = true)
    {
        if (isset($this->_accesses[$permission])) {
            return $this->_accesses[$permission];
        }

        $access = false;

        if ($checker = $this->getAccessChecker()) {
            $access = $checker->checkAccess($this->getId(), $permission, $params);

            if ($caching) {
                $this->_accesses[$permission] = $access;
            }
        }

        return $access;
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->getId();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->get($this->idColumn) ?: 0;
    }

    /**
     * @return bool
     */
    public function isLogin(): bool
    {
        return \count($this->data) !== 0;
    }

    /**
     * @return bool
     */
    public function isGuest(): bool
    {
        return !$this->isLogin();
    }

    /**
     * @param bool|false $force
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function refreshIdentity($force = false)
    {
        if (!$this->_storage->has($this->sessKey)) {
            return;
        }

        $id = $this->getId();
        $this->clear();

        /* @var $class IdentityInterface */
        $class = $this->identityClass;

        if (!$force && ($data = $this->_storage->get($this->sessKey))) {
            $this->sets($data);
        } elseif ($id && ($user = $class::findIdentity($id))) {
            $this->setIdentity($user);
        } else {
            throw new \RuntimeException('The refresh auth data is failure!!');
        }
    }

    /**
     * @param IdentityInterface $identity
     * @throws \InvalidArgumentException
     */
    public function setIdentity(IdentityInterface $identity = null)
    {
        if ($identity instanceof IdentityInterface) {
            $this->sets($identity->all());
            $this->_storage->set($this->sessKey, $this->all());
            $this->_accesses = [];
        } elseif ($identity === null) {
            $this->data = [];
        } else {
            throw new \InvalidArgumentException('The identity object must implement IdentityInterface.');
        }
    }

    /**
     * @param array $data
     * @return void
     */
    protected function sets(array $data)
    {
        // except column at set.
        foreach ($this->excepted as $column) {
            if (isset($data[$column])) {
                unset($data[$column]);
            }
        }

        $this->data = $data;
    }

    /**
     * get all data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * clear
     */
    public function clear()
    {
        $this->data = $this->_accesses = [];
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @return CheckAccessInterface
     */
    public function getAccessChecker(): CheckAccessInterface
    {
        return $this->accessChecker; // ? : \Slim::get('accessChecker');
    }

    /**
     * @param CheckAccessInterface $accessChecker
     */
    public function setAccessChecker(CheckAccessInterface $accessChecker)
    {
        $this->accessChecker = $accessChecker;
    }

    /**
     * @return string
     */
    public function getLogoutTo(): string
    {
        return $this->logoutTo;
    }

    /**
     * @param $url
     */
    public function setLogoutTo($url)
    {
        $this->logoutTo = trim($url);
    }

    /**
     * @return string
     */
    public function getLoggedTo(): string
    {
        return $this->loggedTo;
    }

    /**
     * @param $url
     */
    public function setLoggedTo($url)
    {
        $this->loggedTo = trim($url);
    }

    /**
     * @return StorageInterface
     * @throws \RuntimeException
     */
    public function getStorage(): StorageInterface
    {
        if (!$this->_storage) {
            $this->_storage = new SessionStorage();
        }

        return $this->_storage;
    }

    /**
     * @param StorageInterface $_storage
     */
    public function setStorage(StorageInterface $_storage)
    {
        $this->_storage = $_storage;
    }

    /**
     * @return array
     */
    public function getAccesses(): array
    {
        return $this->_accesses;
    }

    /**
     * @param array $accesses
     */
    public function setAccesses(array $accesses)
    {
        $this->_accesses = $accesses;
    }

    /**
     * @param $name
     * @return mixed
     */
    // public function __get($name)
    // {
    //     $getter = 'get' . ucfirst($name);
    //
    //     if (\method_exists($this, $getter)) {
    //         return $this->$getter();
    //     }
    //
    //     return parent::__get($name);
    // }

    /**
     * @return bool
     */
    public function isAutoload(): bool
    {
        return $this->autoload;
    }

    /**
     * @param bool $autoload
     */
    public function setAutoload($autoload)
    {
        $this->autoload = (bool)$autoload;
    }

    /**
     * @return array
     */
    public function getExcepted(): array
    {
        return $this->excepted;
    }

    /**
     * @param array $excepted
     */
    public function setExcepted(array $excepted)
    {
        $this->excepted = $excepted;
    }

    /**
     * @return string
     */
    public function getSessKey(): string
    {
        return $this->sessKey;
    }

    /**
     * @param string $sessKey
     */
    public function setSessKey(string $sessKey)
    {
        $this->sessKey = $sessKey;
    }

}
