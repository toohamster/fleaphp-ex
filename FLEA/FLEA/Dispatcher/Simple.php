<?php

namespace FLEA\Dispatcher;

/**
 * \FLEA\Dispatcher\Simple 分析 HTTP 请求，并转发到合适的 Controller 对象处理
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class Simple
{
    /**
     * 保存了请求信息的数组
     *
     * @var array
     */
    public $_request;

    /**
     * 原始的请求信息数组
     *
     * @var array
     */
    public $_requestBackup;

    /**
     * 构造函数
     *
     * @param array $request
     *
     * @return \FLEA\Dispatcher\Simple
     */
    public function __construct(array &$request)
    {
        $this->_requestBackup =& $request;

        $controllerAccessor = strtolower(\FLEA::getAppInf('controllerAccessor'));
        $actionAccessor = strtolower(\FLEA::getAppInf('actionAccessor'));

        $r = array_change_key_case($request, CASE_LOWER);
        $data = array('controller' => null, 'action' => null);
        if (isset($r[$controllerAccessor])) {
            $data['controller'] = $r[$controllerAccessor];
        }
        if (isset($r[$actionAccessor])) {
            $data['action'] = $r[$actionAccessor];
        }
        $this->_request = $data;
    }

    /**
     * 从请求中分析 Controller、Action 和 Package 名字，然后执行指定的 Action 方法
     *
     * @return mixed
     */
    public function dispatching(): mixed
    {
        $controllerName = $this->getControllerName();
        $actionName = $this->getActionName();
        return $this->_executeAction($controllerName, $actionName, $this->getControllerClass($controllerName));
    }

    /**
     * 执行指定的 Action 方法
     *
     * @param string $controllerName
     * @param string $actionName
     * @param string $controllerClass
     *
     * @return mixed
     */
    protected function _executeAction(string $controllerName, string $actionName, string $controllerClass)
    {
        $callback = \FLEA::getAppInf('dispatcherFailedCallback');

        // 确定动作方法名
        $actionPrefix = \FLEA::getAppInf('actionMethodPrefix');
        $actionMethod = $actionPrefix . $actionName . \FLEA::getAppInf('actionMethodSuffix');

        $controller = null;
        do {
            // 使用 Composer PSR-4 自动加载器加载控制器类
            if (!$this->_loadController($controllerClass)) { break; }

            // 构造控制器对象
            \FLEA::setAppInf('FLEA.internal.currentControllerName', $controllerName);
            \FLEA::setAppInf('FLEA.internal.currentActionName', $actionName);
            $controller = new $controllerClass($controllerName);
            if (!method_exists($controller, $actionMethod)) { break; }
            if (method_exists($controller, '__setController')) {
                $controller->__setController($controllerName, $actionName);
            }
            if (method_exists($controller, '__setDispatcher')) {
                $controller->__setDispatcher($this);
            }

            // 调用 _beforeExecute() 方法
            if (method_exists($controller, '_beforeExecute')) {
                $controller->_beforeExecute($actionMethod);
            }
            // 执行 action 方法
            $ret = $controller->{$actionMethod}();
            // 调用 _afterExecute() 方法
            if (method_exists($controller, '_afterExecute')) {
                $controller->_afterExecute($actionMethod);
            }
            return $ret;
        } while (false);

        if ($callback) {
            // 检查是否调用应用程序设置的错误处理程序
            $args = array($controllerName, $actionName, $controllerClass);
            return call_user_func_array($callback, $args);
        }

        if (is_null($controller)) {
            throw new \FLEA\Exception\MissingController(
                    $controllerName, $actionName, $this->_requestBackup,
                    $controllerClass, $actionMethod, null);
        }

        throw new \FLEA\Exception\MissingAction(
                $controllerName, $actionName, $this->_requestBackup,
                $controllerClass, $actionMethod, null);
    }

    /**
     * 从请求中取得 Controller 名字
     *
     * 如果没有指定 Controller 名字，则返回配置文件中定义的默认 Controller 名字。
     *
     * @return string
     */
    public function getControllerName(): string
    {
        $controllerName = preg_replace('/[^a-z0-9_]+/i', '', $this->_request['controller']);
        if ($controllerName == '') {
            $controllerName = \FLEA::getAppInf('defaultController');
        }
        if (\FLEA::getAppInf('urlLowerChar')) {
            $controllerName = strtolower($controllerName);
        }
        return $controllerName;
    }

    /**
     * 设置要访问的控制器名字
     *
     * @param string $controllerName
     */
    public function setControllerName(string $controllerName): void
    {
        $this->_request['controller'] = $controllerName;
    }

    /**
     * 从请求中取得 Action 名字
     *
     * 如果没有指定 Action 名字，则返回配置文件中定义的默认 Action 名字。
     *
     * @return string
     */
    public function getActionName(): string
    {
        $actionName = preg_replace('/[^a-z0-9]+/i', '', $this->_request['action']);
        if ($actionName == '') {
            $actionName = \FLEA::getAppInf('defaultAction');
        }
        return $actionName;
    }

    /**
     * 设置要访问的动作名字
     *
     * @param string $actionName
     */
    public function setActionName(string $actionName): void
    {
        $this->_request['action'] = $actionName;
    }

    /**
     * 返回指定控制器对应的类名称
     *
     * @param string $controllerName
     *
     * @return string
     */
    public function getControllerClass(string $controllerName): string
    {
        $controllerClass = \FLEA::getAppInf('controllerClassPrefix');
        if (\FLEA::getAppInf('urlLowerChar')) {
            $controllerClass .= ucfirst(strtolower($controllerName));
        } else {
            $controllerClass .= $controllerName;
        }
        return $controllerClass . 'Controller';
    }

    /**
     * 分析 url 地址，找出控制器名字和动作名
     *
     * @param string $url
     *
     * @return array
     */
    public function parseUrl(string $url): array
    {
        $url = parse_url($url);
        $args = [];
        parse_str($url['query'], $args);
        $args = array_change_key_case($args, CASE_LOWER);
        $controllerAccessor = strtolower(\FLEA::getAppInf('controllerAccessor'));
        $actionAccessor = strtolower(\FLEA::getAppInf('actionAccessor'));

        $controllerName = isset($args[$controllerAccessor]) ?
                $args[$controllerAccessor] : null;
        $actionName = isset($args[$actionAccessor]) ?
                $args[$actionAccessor] : null;

        unset($args[$controllerAccessor]);
        unset($args[$actionAccessor]);
        return array($controllerName, $actionName, $args);
    }

    /**
     * 载入控制器类
     *
     * 使用 Composer PSR-4 自动加载器
     *
     * @param string $controllerClass
     *
     * @return boolean
     */
    protected function _loadController(string $controllerClass): bool
    {
        // 使用 Composer PSR-4 自动加载器加载类
        if (!class_exists($controllerClass, true)) {
            return false;
        }
        return true;
    }
}
