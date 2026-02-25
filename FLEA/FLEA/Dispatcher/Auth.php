<?php

namespace FLEA\Dispatcher;

/**
 * \FLEA\Dispatcher\Auth 分析 HTTP 请求，并转发到合适的 Controller 对象处理
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class Auth extends \FLEA\Dispatcher\Simple
{
    /**
     * 用于提供验证服务的对象实例
     *
     * @var \FLEA\Rbac
     */
    public $_auth;

    /**
     * 构造函数
     *
     * @param array $request
     *
     * @return \FLEA\Dispatcher\Auth
     */
    public function __construct(array $request)
    {
        parent::__construct($request);
        $this->_auth = \FLEA::getSingleton(\FLEA::getAppInf('dispatcherAuthProvider'));
    }

    /**
     * 返回当前使用的验证服务对象
     *
     * @return \FLEA\Rbac
     */
    public function getAuthProvider(): \FLEA\Rbac
    {
        return $this->_auth;
    }

    /**
     * 设置要使用的验证服务对象
     *
     * @param \FLEA\Rbac $auth
     */
    public function setAuthProvider(\FLEA\Rbac $auth): void
    {
        $this->_auth = $auth;
    }

    /**
     * 通过验证服务对象的 setUser 方法将用户数据保存到 session 中
     *
     * @param array $userData
     * @param mixed $rolesData
     */
    public function setUser(array $userData, $rolesData = null): void
    {
        $this->_auth->setUser($userData, $rolesData);
    }

    /**
     * 通过验证服务对象的 getUser 方法从 session 中获取保存的用户数据
     *
     * @return array
     */
    public function getUser(): array
    {
        return $this->_auth->getUser();
    }

    /**
     * 通过验证服务对象的 getRolesArray 方法从 session 中获取保存的用户角色数据
     *
     * @return array
     */
    public function getUserRoles(): array
    {
        return $this->_auth->getRolesArray();
    }

    /**
     * 通过验证服务对象的 getUser 方法清理保存在 session 中的用户数据
     *
     * @return array
     */
    public function clearUser()
    {
        $this->_auth->clearUser();
    }

    /**
     * 执行控制器方法
     *
     * @return mixed
     */
    public function dispatching()
    {
        $controllerName  = $this->getControllerName();
        $actionName      = $this->getActionName();
        $controllerClass = $this->getControllerClass($controllerName);

        if ($this->check($controllerName, $actionName, $controllerClass)) {
            // 检查通过，执行控制器方法
            return $this->_executeAction($controllerName, $actionName, $controllerClass);
        } else {
            // 检查失败
            $callback = \FLEA::getAppInf('dispatcherAuthFailedCallback');

            $rawACT = $this->getControllerACT($controllerName, $controllerClass);
            if (is_null($rawACT) || empty($rawACT)) { return true; }
            $ACT = $this->_auth->prepareACT($rawACT);
            $roles = $this->_auth->getRolesArray();
            $args = array($controllerName, $actionName, $controllerClass, $ACT, $roles);

            // 如果控制器定义了的 _onAuthFailed 静态方法，则调用该方法
            if ($this->_loadController($controllerClass)) {
                $methods = get_class_methods($controllerClass);
                if (in_array('_onAuthFailed', $methods, true)) {
                    if (call_user_func_array(array($controllerClass, '_onAuthFailed'), $args) !== false) {
                        return false;
                    }
                }
            }

            if ($callback) {
                return call_user_func_array($callback, $args);
            } else {
                throw new \FLEA\Dispatcher\Exception\CheckFailed($controllerName, $actionName, $rawACT, $roles);
            }
        }
    }

    /**
     * 检查当前用户是否有权限访问指定的控制器和方法
     *
     * 验证步骤如下：
     *
     * 1、通过 authProiver 获取当前用户的角色信息；
     * 2、调用 getControllerACT() 获取指定控制器的访问控制表；
     * 3、根据 ACT 对用户角色进行检查，通过则返回 true，否则返回 false。
     *
     * @param string $controllerName
     * @param string $actionName
     * @param string $controllerClass
     *
     * @return boolean
     */
    public function check($controllerName, $actionName = null, $controllerClass = null)
    {
        if (is_null($controllerClass)) {
            $controllerClass = $this->getControllerClass($controllerName);
        }
        if (is_null($actionName)) {
            $actionName = $this->getActionName();
        }
        // 如果控制器没有提供 ACT，或者提供了一个空的 ACT，则假定允许用户访问
        $rawACT = $this->getControllerACT($controllerName, $controllerClass);
        if (is_null($rawACT) || empty($rawACT)) { return true; }

        $ACT = $this->_auth->prepareACT($rawACT);
        $ACT['actions'] = [];
        if (isset($rawACT['actions']) && is_array($rawACT['actions'])) {
            foreach ($rawACT['actions'] as $rawActionName => $rawActionACT) {
                if ($rawActionName !== ACTION_ALL) {
                    $rawActionName = strtolower($rawActionName);
                }
                $ACT['actions'][$rawActionName] = $this->_auth->prepareACT($rawActionACT);
            }
        }
        // 取出用户角色信息
        $roles = $this->_auth->getRolesArray();
        // 首先检查用户是否可以访问该控制器
        if (!$this->_auth->check($roles, $ACT)) { return false; }

        // 接下来验证用户是否可以访问指定的控制器方法
        $actionName = strtolower($actionName);
        if (isset($ACT['actions'][$actionName])) {
            return $this->_auth->check($roles, $ACT['actions'][$actionName]);
        }

        // 如果当前要访问的控制器方法没有在 act 中指定，则检查 act 中是否提供了 ACTION_ALL
        if (!isset($ACT['actions'][ACTION_ALL])) { return true; }
        return $this->_auth->check($roles, $ACT['actions'][ACTION_ALL]);
    }

    /**
     * 获取指定控制器的访问控制表（ACT）
     *
     * @param string $controllerName
     * @param string $controllerClass
     *
     * @return array
     */
    public function getControllerACT($controllerName, $controllerClass)
    {
        // 首先尝试从全局 ACT 查询控制器的 ACT
        $ACT = \FLEA::getAppInfValue('globalACT', $controllerName);
        if ($ACT) { return $ACT; }

        // 将控制器类名转换为文件路径
        $actFilename = str_replace('\\', DIRECTORY_SEPARATOR, $controllerClass) . '.act.php';
        
        if (!file_exists($actFilename)) {
            if (\FLEA::getAppInf('autoQueryDefaultACTFile')) {
                $ACT = $this->getControllerACTFromDefaultFile($controllerName);
                if ($ACT) { return $ACT; }
            }

            if (\FLEA::getAppInf('controllerACTLoadWarning')) {
                trigger_error(sprintf(_ET(0x0701006), $controllerName), E_USER_WARNING);
            }
            return \FLEA::getAppInf('defaultControllerACT');
        }

        return $this->_loadACTFile($actFilename);
    }

    /**
     * 从默认 ACT 文件中载入指定控制器的 ACT
     *
     * @param string $controllerName
     */
    public function getControllerACTFromDefaultFile($controllerName)
    {
        $actFilename = realpath(\FLEA::getAppInf('defaultControllerACTFile'));
        if (!$actFilename) {
            if (\FLEA::getAppInf('controllerACTLoadWarning')) {
                trigger_error(sprintf(_ET(0x0701006), $controllerName), E_USER_WARNING);
            }
            return \FLEA::getAppInf('defaultControllerACT');
        }

        $ACT = $this->_loadACTFile($actFilename);
        if ($ACT === false) { return false; }

        $ACT = array_change_key_case($ACT, CASE_UPPER);
        $controllerName = strtoupper($controllerName);
        return isset($ACT[$controllerName]) ?
            $ACT[$controllerName] :
            \FLEA::getAppInf('defaultControllerACT');
    }

    /**
     * 载入 ACT 文件
     *
     * @param string $actFilename
     *
     * @return mixed
     */
    protected function _loadACTFile($actFilename)
    {
        static $files = [];

        if (isset($files[$actFilename])) {
            return $files[$actFilename];
        }

        $ACT = require($actFilename);
        if (is_array($ACT)) {
            $files[$actFilename] = $ACT;
            return $ACT;
        }

        // 当控制器的 ACT 文件没有返回 ACT 时抛出异常
        throw new \FLEA\Rbac\Exception\InvalidACTFile($actFilename, $ACT);
    }
}
