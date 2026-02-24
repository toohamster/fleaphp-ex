<?php

namespace FLEA\Controller;

/**
 * 定义 \FLEA\Controller\Action 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Action.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Controller\Action 实现了一个其它控制器的超类，
 * 为开发者自己的控制器提供了一些方便的成员变量和方法
 *
 * 开发者不一定需要从这个类继承来构造自己的控制器。
 * 但从这个类派生自己的控制器可以获得一些便利性。
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class Action
{
    /**
     * 当前控制的名字，用于 $this->url() 方法
     *
     * @var string
     */
    public $_controllerName = null;

    /**
     * 当前调用的动作名
     *
     * @var string
     */
    public $_actionName = null;

    /**
     * 当前使用的调度器的名字
     *
     * @var \FLEA\Dispatcher\Auth
     */
    public $_dispatcher = null;

    /**
     * 要使用的控制器部件
     *
     * @var array
     */
    public $components = [];

    /**
     * 渲染视图前要调用的 callback 方法
     *
     * @var array
     */
    public $_renderCallbacks = [];

    /**
     * 构造函数
     *
     * @param string $controllerName
     *
     * @return \FLEA\Controller\Action
     */
    public function __construct(string $controllerName)
    {
        $this->_controllerName = $controllerName;

        foreach ((array)$this->components as $componentName) {
            $this->{$componentName} = $this->_getComponent($componentName);
        }
    }

    /**
     * 获得指定的组件对象
     *
     * @param string $componentName
     *
     * @return object
     */
    protected function _getComponent(string $componentName): object
    {
        static $instances = [];

        if (!isset($instances[$componentName])) {
            $componentClassName = FLEA::getAppInf('component.' . $componentName);
            // 使用 Composer PSR-4 自动加载
            if (!class_exists($componentClassName, false)) {
                throw new \FLEA\Exception\ExpectedClass($componentClassName);
            }
            $instances[$componentName] = new $componentClassName($this);
        }
        return $instances[$componentName];
    }

    /**
     * 设置控制器名字，由 dispatcher 调用
     *
     * @param string $controllerName
     * @param string $actionName
     */
    public function __setController(string $controllerName, string $actionName): void
    {
        $this->_controllerName = $controllerName;
        $this->_actionName = $actionName;
    }

    /**
     * 设置当前控制器使用的调度器对象
     *
     * @param \FLEA\Dispatcher\Simple $dispatcher
     */
    public function __setDispatcher(\FLEA\Dispatcher\Simple &$dispatcher): void
    {
        $this->_dispatcher =& $dispatcher;
    }

    /**
     * 获得当前使用的 Dispatcher
     *
     * @return \FLEA\Dispatcher\Auth
     */
    protected function _getDispatcher(): \FLEA\Dispatcher\Auth
    {
        if (!is_object($this->_dispatcher)) {
            $this->_dispatcher = FLEA::getSingleton(FLEA::getAppInf('dispatcher'));
        }
        return $this->_dispatcher;
    }

    /**
     * 构造当前控制器的 url 地址
     *
     * @param string $actionName
     * @param array $args
     * @param string $anchor
     *
     * @return string
     */
    protected function _url(?string $actionName = null, ?array $args = null, ?string $anchor = null): string
    {
        return url($this->_controllerName, $actionName, $args, $anchor);
    }

    /**
     * 转发请求到另一个控制器方法
     *
     * @param string $controllerName
     * @param string $actionName
     */
    protected function _forward(?string $controllerName = null, ?string $actionName = null): void
    {
        $this->_dispatcher->setControllerName($controllerName);
        $this->_dispatcher->setActionName($actionName);
        $this->_dispatcher->dispatching();
    }

    /**
     * 返回视图对象
     *
     * @return object
     */
    protected function _getView(): object
    {
        $viewClass = FLEA::getAppInf('view');
        if ($viewClass != 'PHP') {
            return FLEA::getSingleton($viewClass);
        } else {
            $view = false;
            return $view;
        }
    }

    /**
     * 执行指定的视图
     *
     * @param string $__flea_internal_viewName
     * @param array $data
     */
    protected function _executeView(string $__flea_internal_viewName, ?array $data = null): void
    {
        $viewClass = FLEA::getAppInf('view');
        if ($viewClass == 'PHP') {
            if (strtolower(substr($__flea_internal_viewName, -4)) != '.php') {
                $__flea_internal_viewName .= '.php';
            }
            $view = null;
            foreach ((array)$this->_renderCallbacks as $callback) {
                call_user_func_array($callback, array(& $data, & $view));
            }
            if (is_array($data)) { extract($data); }
            include($__flea_internal_viewName);
        } else {
            $view = $this->_getView();
            foreach ((array)$this->_renderCallbacks as $callback) {
                call_user_func_array($callback, array(& $data, & $view));
            }
            if (is_array($data)) { $view->assign($data); }
            $view->display($__flea_internal_viewName);
        }
    }

    /**
     * 判断 HTTP 请求是否是 POST 方法
     *
     * @return boolean
     */
    protected function _isPOST(): bool
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    /**
     * 判断 HTTP 请求是否是通过 XMLHttp 发起的
     *
     * @return boolean
     */
    protected function _isAjax(): bool
    {
        $r = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
        return $r == 'xmlhttprequest';
    }

    /**
     * 为指定控件绑定事件，返回浏览器端该事件响应函数的名字
     *
     * @param string $controlName
     * @param string $event
     * @param string $action
     * @param array $attribs
     *
     * @return string
     */
    protected function _registerEvent(string $controlName, string $event, string $action, ?array $attribs = null): string
    {
        $ajax = FLEA::initAjax();
        return $ajax->registerEvent($controlName, $event,
                url($this->_controllerName, $action), $attribs);
    }

    /**
     * 注册一个视图渲染 callback 方法
     *
     * @param callback $callback
     */
    protected function _registerRenderCallback($callback): void
    {
        $this->_renderCallbacks[] = $callback;
    }
}
