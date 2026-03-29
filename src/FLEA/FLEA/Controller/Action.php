<?php

namespace FLEA\Controller;

/**
 * 控制器基类
 *
 * 所有控制器的基类，提供常用功能和辅助方法。
 *
 * 主要功能：
 * - 组件加载和管理
 * - 视图渲染和转发
 * - URL 生成
 * - 请求类型判断
 *
 * 用法示例：
 * ```php
 * class PostController extends \FLEA\Controller\Action
 * {
 *     public function actionIndex()
 *     {
 *         // 获取视图对象
 *         $view = $this->getView();
 *         $view->assign('posts', $this->getPosts());
 *         $view->display('post/index.php');
 *     }
 *
 *     public function actionShow()
 *     {
 *         // 转发到另一个动作
 *         $this->forward('Post', 'index');
 *     }
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Action
{
    /**
     * @var string 当前控制器的名称
     */
    protected string $controllerName = '';

    /**
     * @var string 当前调用的动作名称
     */
    protected string $actionName = '';

    /**
     * @var \FLEA\Dispatcher\Simple|null 当前使用的调度器
     */
    protected ?\FLEA\Dispatcher\Simple $dispatcher = null;

    /**
     * @var array 要使用的控制器组件列表
     */
    public $components = [];

    /**
     * @var array 渲染视图前要调用的回调函数
     */
    protected array $renderCallbacks = [];

    /**
     * 构造函数
     *
     * @param string $controllerName 控制器名称
     */
    public function __construct(string $controllerName)
    {
        $this->controllerName = $controllerName;

        foreach ((array)$this->components as $componentName) {
            $this->{$componentName} = $this->getComponent($componentName);
        }
    }

    /**
     * 获取组件对象
     *
     * 组件会被缓存，重复调用返回同一实例。
     *
     * @param string $componentName 组件名称
     *
     * @return object 组件实例
     *
     * @throws \FLEA\Exception\ExpectedClass 组件类不存在时抛出
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
     * 设置控制器名称
     *
     * 由 Dispatcher 调用，用于设置当前控制器和动作名称。
     *
     * @param string $controllerName 控制器名称
     * @param string $actionName     动作名称
     *
     * @return void
     */
    public function setController(string $controllerName, string $actionName): void
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
    }

    /**
     * 设置调度器
     *
     * 设置当前控制器使用的调度器对象。
     *
     * @param \FLEA\Dispatcher\Simple $dispatcher 调度器实例
     *
     * @return void
     */
    public function setDispatcher(\FLEA\Dispatcher\Simple $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * 获取调度器
     *
     * 获取当前使用的 Dispatcher 实例。
     *
     * @return \FLEA\Dispatcher\Simple|null 调度器实例
     */
    protected function getDispatcher(): ?\FLEA\Dispatcher\Simple
    {
        if (!is_object($this->dispatcher)) {
            $this->dispatcher = \FLEA::getSingleton(\FLEA::getAppInf('dispatcher'));
        }
        return $this->dispatcher;
    }

    /**
     * 生成 URL
     *
     * 构造当前控制器的 URL 地址。
     *
     * 用法示例：
     * ```php
     * // 生成当前控制器默认动作的 URL
     * $url = $this->url();
     *
     * // 生成指定动作的 URL
     * $url = $this->url('show', ['id' => 123]);
     *
     * // 带锚点
     * $url = $this->url('show', ['id' => 123], 'comments');
     * ```
     *
     * @param string|null $actionName 动作名称（省略时为当前控制器）
     * @param array|null  $args       URL 参数数组
     * @param string|null $anchor     URL 锚点
     *
     * @return string 生成的 URL
     */
    protected function url(?string $actionName = null, ?array $args = null, ?string $anchor = null): string
    {
        return url(
            $actionName ? $this->controllerName . '.' . $actionName : $this->controllerName,
            $args ?? []
        );
    }

    /**
     * 转发到另一个控制器方法
     *
     * 内部转发到另一个控制器的 Action 方法执行。
     *
     * 用法示例：
     * ```php
     * // 转发到当前控制器的另一个动作
     * $this->forward(null, 'index');
     *
     * // 转发到另一个控制器
     * $this->forward('User', 'login');
     * ```
     *
     * @param string|null $controllerName 目标控制器名称（省略时保持当前控制器）
     * @param string|null $actionName     目标动作名称
     *
     * @return void
     */
    protected function forward(?string $controllerName = null, ?string $actionName = null): void
    {
        $this->dispatcher->setControllerName($controllerName);
        $this->dispatcher->setActionName($actionName);
        $this->dispatcher->dispatching();
    }

    /**
     * 获取视图对象
     *
     * 根据配置返回视图引擎实例。
     *
     * @return \FLEA\View\ViewInterface 视图对象实例
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
     * 执行视图渲染
     *
     * 执行指定的视图文件并输出。
     * 支持 PHP 原生视图和视图引擎两种模式。
     *
     * 用法示例：
     * ```php
     * // 渲染视图
     * $this->executeView('post/show.php');
     *
     * // 带数据渲染
     * $this->executeView('post/show.php', ['post' => $postData]);
     * ```
     *
     * @param string     $__flea_internal_viewName 视图文件路径
     * @param array|null $data                      视图数据数组
     *
     * @return void
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
     * 判断是否为 POST 请求
     *
     * @return bool POST 请求返回 true，否则返回 false
     */
    protected function isPost(): bool
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    /**
     * 判断是否为 Ajax 请求
     *
     * 检查 X-Requested-With 头是否为 XMLHttpRequest。
     *
     * @return bool Ajax 请求返回 true，否则返回 false
     */
    protected function isAjax(): bool
    {
        $r = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        return $r == 'xmlhttprequest';
    }

    /**
     * 注册渲染回调函数
     *
     * 回调函数会在视图渲染前被调用。
     *
     * @param callable $callback 回调函数
     *
     * @return void
     */
    protected function registerRenderCallback($callback): void
    {
        $this->renderCallbacks[] = $callback;
    }
}
