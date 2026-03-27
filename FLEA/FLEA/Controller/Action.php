<?php

namespace FLEA\Controller;

class Action
{
    /**
     * 当前控制的名字，用于 $this->url() 方法
     * @var string
     */
    protected string $controllerName = '';

    /**
     * 当前调用的动作名
     * @var string
     */
    protected string $actionName = '';

    /**
     * 当前使用的调度器的名字
     * @var \FLEA\Dispatcher\Simple|null
     */
    protected ?\FLEA\Dispatcher\Simple $dispatcher = null;

    /**
     * 要使用的控制器部件
     * @var array
     */
    public $components = [];

    /**
     * 渲染视图前要调用的 callback 方法
     * @var array
     */
    protected array $renderCallbacks = [];

    /**
     * 构造函数
     * @param string $controllerName
     */
    public function __construct(string $controllerName)
    {
        $this->controllerName = $controllerName;

        foreach ((array)$this->components as $componentName) {
            $this->{$componentName} = $this->getComponent($componentName);
        }
    }

    /**
     * 获得指定的组件对象
     * @param string $componentName
     * @return object
     */
    protected function getComponent(string $componentName): object
    {
        static $instances = [];

        if (!isset($instances[$componentName])) {
            $componentClassName = \FLEA::getAppInf('component.' . $componentName);
            // 使用 Composer PSR-4 自动加载
            if (!class_exists($componentClassName, true)) {
                throw new \FLEA\Exception\ExpectedClass($componentClassName);
            }
            $instances[$componentName] = new $componentClassName($this);
        }
        return $instances[$componentName];
    }

    /**
     * 设置控制器名字，由 dispatcher 调用
     * @param string $controllerName
     * @param string $actionName
     */
    public function setController(string $controllerName, string $actionName): void
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
    }

    /**
     * 设置当前控制器使用的调度器对象
     * @param \FLEA\Dispatcher\Simple $dispatcher
     */
    public function setDispatcher(\FLEA\Dispatcher\Simple $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * 获得当前使用的 Dispatcher
     * @return \FLEA\Dispatcher\Simple|null
     */
    protected function getDispatcher(): ?\FLEA\Dispatcher\Simple
    {
        if (!is_object($this->dispatcher)) {
            $this->dispatcher = \FLEA::getSingleton(\FLEA::getAppInf('dispatcher'));
        }
        return $this->dispatcher;
    }

    /**
     * 构造当前控制器的 url 地址
     * @param string|null $actionName
     * @param array|null $args
     * @param string|null $anchor
     * @return string
     */
    protected function url(?string $actionName = null, ?array $args = null, ?string $anchor = null): string
    {
        return url(
            $actionName ? $this->controllerName . '.' . $actionName : $this->controllerName,
            $args ?? []
        );
    }

    /**
     * 转发请求到另一个控制器方法
     * @param string|null $controllerName
     * @param string|null $actionName
     */
    protected function forward(?string $controllerName = null, ?string $actionName = null): void
    {
        $this->dispatcher->setControllerName($controllerName);
        $this->dispatcher->setActionName($actionName);
        $this->dispatcher->dispatching();
    }

    /**
     * 返回视图对象
     * @return \FLEA\View\ViewInterface
     */
    protected function getView(): \FLEA\View\ViewInterface
    {
        $viewClass = \FLEA::getAppInf('view');
        if ($viewClass != 'PHP') {
            return \FLEA::getSingleton($viewClass);
        } else {
            return new \FLEA\View\NullView();
        }
    }

    /**
     * 执行指定的视图
     * @param string $__flea_internal_viewName
     * @param array|null $data
     */
    protected function executeView(string $__flea_internal_viewName, ?array $data = null): void
    {
        $viewClass = \FLEA::getAppInf('view');
        if ($viewClass == 'PHP') {
            if (strtolower(substr($__flea_internal_viewName, -4)) != '.php') {
                $__flea_internal_viewName .= '.php';
            }
            $view = null;
            foreach ((array)$this->renderCallbacks as $callback) {
                call_user_func_array($callback, array(& $data, $view));
            }
            if (is_array($data)) { extract($data); }
            include($__flea_internal_viewName);
        } else {
            $view = $this->getView();
            foreach ((array)$this->renderCallbacks as $callback) {
                call_user_func_array($callback, array(& $data, $view));
            }
            if (is_array($data)) { $view->assign($data); }
            $view->display($__flea_internal_viewName);
        }
    }

    /**
     * 判断 HTTP 请求是否是 POST 方法
     * @return bool
     */
    protected function isPost(): bool
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    /**
     * 判断 HTTP 请求是否是通过 XMLHttp 发起的
     * @return bool
     */
    protected function isAjax(): bool
    {
        $r = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        return $r == 'xmlhttprequest';
    }

    protected function registerRenderCallback($callback): void
    {
        $this->renderCallbacks[] = $callback;
    }
}
