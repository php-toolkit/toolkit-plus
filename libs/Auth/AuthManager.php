<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/6 0006
 * Time: 21:57
 */

namespace ToolkitPlus\Auth;

use Toolkit\Collection\CollectionInterface;
use Toolkit\Collection\SimpleCollection;
use Toolkit\ObjUtil\Obj;

/**
 * Class AuthManager
 * @package ToolkitPlus\Auth
 * @property int id
 */
class AuthManager extends SimpleCollection implements AuthManagerInterface
{
    /**
     * @var string The session key
     */
    protected static $sessKey = '_user_auth_data';

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
     * user data persistent storage driver Bridge
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var bool
     * true   Auto load logged user data from storage.
     * false  You need manual load user data on before the first use
     */
    private $autoload = true;

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
     * Exclude fields that don't need to be saved.
     * @var array
     */
    protected $excepted = ['password'];

    /**
     * @var CheckAccessInterface
     */
    private $accessChecker;

    const AFTER_LOGGED_TO_KEY = '_after_logged_to';
    const AFTER_LOGOUT_TO_KEY = '_after_logout_to';

    /**
     * don't allow set attribute
     * @param array $options
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        parent::__construct();

        Obj::init($this, $options);

        if ($this->identityClass === null) {
            throw new \InvalidArgumentException('The property "identityClass" must be set');
        }

        // if enable autoload and user have already login
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

        unset($_SESSION[static::$sessKey]);
    }

    /**
     * check user permission
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
     * clear
     */
    public function clear()
    {
        $this->data = $this->_accesses = [];
    }

    /**
     * @param bool|false $force
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function refreshIdentity($force = false)
    {
        if (!$this->storage->has(self::$sessKey)) {
            return;
        }

        $id = $this->getId();
        $this->clear();

        /* @var $class IdentityInterface */
        $class = $this->identityClass;

        if (!$force && ($data = $this->storage->get(self::$sessKey))) {
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
            $this->sets((array)$identity);
            $this->storage->set(self::$sessKey, $this->all());
            $this->_accesses = [];
        } elseif ($identity === null) {
            $this->data = [];
        } else {
            throw new \InvalidArgumentException('The identity object must implement IdentityInterface.');
        }
    }

    /**
     * @param array $data
     * @return CollectionInterface
     */
    public function sets(array $data): CollectionInterface
    {
        // except column at set.
        foreach ($this->excepted as $column) {
            if (isset($data[$column])) {
                unset($data[$column]);
            }
        }

        return parent::sets($data);
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
        if (!$this->storage) {
            $this->storage = new SessionStorage();
        }

        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
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
    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);

        if (\method_exists($this, $getter)) {
            return $this->$getter();
        }

        return parent::__get($name);
    }

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

}
