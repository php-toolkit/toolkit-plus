<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/6 0006
 * Time: 21:57
 */

namespace ToolkitPlus\Auth;

use Inhere\Exceptions\InvalidArgumentException;
use Inhere\Exceptions\InvalidConfigException;
use Inhere\Library\Collections\CollectionInterface;
use Inhere\Library\Collections\SimpleCollection;
use Inhere\Library\Helpers\Obj;

/**
 * Class User
 * @package ToolkitPlus\Auth
 * @property int id
 */
class User extends SimpleCollection
{
    /**
     * @var string
     */
    protected static $saveKey = '_user_auth_data';

    /**
     * Exclude fields that don't need to be saved.
     * @var array
     */
    protected $excepted = ['password'];

    /**
     * the identity [model] class name
     * @var string
     */
    public $identityClass;

    /**
     * @var string
     */
    public $loginUrl = '/login';

    /**
     * @var string
     */
    protected $loggedTo = '/';

    /**
     * @var string
     */
    public $logoutUrl = '/logout';

    /**
     * @var string
     */
    protected $logoutTo = '/';

    /**
     * @var CheckAccessInterface
     */
    public $accessChecker;

    /**
     * @var string
     */
    public $idColumn = 'id';

    /** @var StorageInterface */
    private $storage;

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

    const AFTER_LOGGED_TO_KEY = '_after_logged_to';
    const AFTER_LOGOUT_TO_KEY = '_after_logout_to';

    /**
     * don't allow set attribute
     * @param array $options
     * @throws InvalidConfigException
     */
    public function __construct($options = [])
    {
        parent::__construct();

        Obj::setAttrs($this, $options);

        if ($this->identityClass === null) {
            throw new InvalidConfigException('User::identityClass must be set.');
        }

        // if have already login
        if (isset($_SESSION[static::$saveKey])) {
            $this->refreshIdentity();
        }
    }

    /**
     * @param IdentityInterface $user
     * @return bool
     */
    public function login(IdentityInterface $user)
    {
        $this->clear();
        $this->setIdentity($user);

        return $this->isLogin();
    }

    public function logout()
    {
        $this->clear();

        unset($_SESSION[static::$saveKey]);
    }

    /*
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws InvalidConfigException
     */
    /*public function loginRequired(Request $request, Response $response)
    {
        $authUrl = Slim::get('config')->get('urls.login', $this->loginUrl);

        if (!$authUrl) {
            throw new InvalidConfigException("require config 'urls.login' !");
        }

        $this->setLoggedTo($request->getRequestUri());
        $msg = Slim::$app->language->tran('needLogin');

        // when is xhr
        if ( $request->isXhr() ) {
            $data = ['redirect' => $authUrl];

            return $response->withJson($data, __LINE__, $msg);
        }

        return $response->withRedirect($authUrl)->withMessage($msg);
    }*/

    /**
     * check user permission
     * @param string $permission a permission name or a url
     * @param array $params
     * @param bool|true $caching
     * @return bool
     */
    public function can($permission, array $params = [], $caching = true)
    {
        return $this->canAccess($permission, $params, $caching);
    }

    /**
     * @param $permission
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
    public function getId()
    {
        return $this->get($this->idColumn) ?: 0;
    }

    /**
     * @return bool
     */
    public function isLogin()
    {
        return \count($this->data) !== 0;
    }

    /**
     * @return bool
     */
    public function isGuest()
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
     */
    public function refreshIdentity($force = false)
    {
        $id = $this->getId();
        $this->clear();

        /* @var $class IdentityInterface */
        $class = $this->identityClass;

        if (!$force && ($data = session(self::$saveKey))) {
            $this->sets($data);
        } elseif ($user = $class::findIdentity($id)) {
            $this->setIdentity($user);
        } else {
            throw new \RuntimeException('The refresh auth data is failure!!');
        }
    }

    /**
     * @param IdentityInterface $identity
     * @throws InvalidArgumentException
     */
    public function setIdentity(IdentityInterface $identity)
    {
        if ($identity instanceof IdentityInterface) {
            $this->sets((array)$identity);
            session([self::$saveKey => $identity->all()]);
            $this->_accesses = [];
        } elseif ($identity === null) {
            $this->data = [];
        } else {
            throw new InvalidArgumentException('The identity object must implement IdentityInterface.');
        }
    }

    /**
     * @param array $data
     * @return CollectionInterface
     */
    public function sets(array $data)
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
    public function getAccessChecker()
    {
        return $this->accessChecker; // ? : \Slim::get('accessChecker');
    }

    /**
     * @return string
     */
    public function getLogoutTo()
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
    public function getLoggedTo()
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
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     */
    public function setStorage($storage)
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

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        return parent::__get($name);
    }
}
