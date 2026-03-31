<?php

namespace FLEA\Dispatcher;

/**
 * 简单调度器
 *
 * 负责解析请求中的 Controller 和 Action 名称，
 * 加载并执行对应的控制器类和方法。
 *
 * 调度流程：
 * 1. 从请求中获取 Controller 和 Action 名称
 * 2. 构造控制器类名
 * 3. 加载控制器类
 * 4. 实例化控制器
 * 5. 调用 beforeExecute()（如果存在）
 * 6. 执行 Action 方法
 * 7. 调用 afterExecute()（如果存在）
 *
 * 用法示例：
 * ```php
 * $dispatcher = new \FLEA\Dispatcher\Simple($_GET);
 * $dispatcher->dispatching();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Simple
{
    /**
     * @var array 请求数据（已过滤）
     */
    protected array $request = [];

    /**
     * @var array 原始请求数据备份
     */
    protected array $requestBackup = [];

    /**
     * 构造函数
     *
     * @param array $request 请求数据数组（$_GET/$_POST）
     */
    public function __construct(array &$request)
    {
        $this->requestBackup =& $request;

        $r = array_change_key_case($request, CASE_LOWER);
        $data = ['controller' => null, 'action' => null];
        if (isset($r[\FLEA\Router::CONTROLLER_KEY])) {
            $data['controller'] = $r[\FLEA\Router::CONTROLLER_KEY];
        }
        if (isset($r[\FLEA\Router::ACTION_KEY])) {
            $data['action'] = $r[\FLEA\Router::ACTION_KEY];
        }
        $this->request = $data;
    }

    /**
     * 执行调度
     *
     * 从请求中分析 Controller、Action 名称，然后执行指定的 Action 方法。
     *
     * @return mixed 控制器 Action 方法的返回值
     */
    public function dispatching()
    {
        $controllerName = $this->getControllerName();
        $actionName = $this->getActionName();
        return $this->executeAction($controllerName, $actionName, $this->getControllerClass($controllerName));
    }

    /**
     * 执行指定的 Action 方法
     *
     * 完整的控制器执行流程：
     * 1. 加载控制器类
     * 2. 实例化控制器
     * 3. 调用 beforeExecute()
     * 4. 执行 Action 方法
     * 5. 调用 afterExecute()
     *
     * @param string $controllerName  控制器名称
     * @param string $actionName      Action 名称
     * @param string $controllerClass 控制器类名
     *
     * @return mixed Action 方法的返回值
     *
     * @throws \FLEA\Exception\MissingController 控制器不存在时抛出
     * @throws \FLEA\Exception\MissingAction     Action 方法不存在时抛出
     */
    protected function executeAction(string $controllerName, string $actionName, string $controllerClass)
    {
        $callback = \FLEA::getAppInf('dispatcherFailedCallback');

        // 将 kebab-case 转换为 PascalCase: user-list → UserList
        $actionName = kebab_to_pascal($actionName);

        // 确定动作方法名（action 前缀 + PascalCase，如 show → actionShow）
        $actionMethod = 'action' . $actionName;

        $controller = null;
        do {
            // 使用 Composer PSR-4 自动加载器加载控制器类
            if (!$this->loadController($controllerClass)) { break; }

            // 构造控制器对象
            \FLEA::setAppInf('FLEA.internal.currentControllerName', $controllerName);
            \FLEA::setAppInf('FLEA.internal.currentActionName', $actionName);
            $controller = new $controllerClass($controllerName);
            if (!method_exists($controller, $actionMethod)) { break; }
            if (method_exists($controller, 'setController')) {
                $controller->setController($controllerName, $actionName);
            }
            if (method_exists($controller, 'setDispatcher')) {
                $controller->setDispatcher($this);
            }

            // 调用 beforeExecute() 方法
            if (method_exists($controller, 'beforeExecute')) {
                $controller->beforeExecute($actionMethod);
            }
            // 执行 action 方法
            $ret = $controller->{$actionMethod}();
            // 调用 afterExecute() 方法
            if (method_exists($controller, 'afterExecute')) {
                $controller->afterExecute($actionMethod);
            }
            return $ret;
        } while (false);

        if ($callback) {
            // 检查是否调用应用程序设置的错误处理程序
            $args = [$controllerName, $actionName, $controllerClass];
            return call_user_func_array($callback, $args);
        }

        if (is_null($controller)) {
            throw new \FLEA\Exception\MissingController(
                    $controllerName, $actionName, $this->requestBackup,
                    $controllerClass, $actionMethod, null);
        }

        throw new \FLEA\Exception\MissingAction(
                $controllerName, $actionName, $this->requestBackup,
                $controllerClass, $actionMethod, null);
    }

    /**
     * 获取 Controller 名称
     *
     * 从请求中获取 Controller 名称，如果未指定则返回配置的默认值。
     * 会自动过滤非法字符。
     *
     * @return string 控制器名称
     */
    public function getControllerName(): string
    {
        $controllerName = preg_replace('/[^a-z0-9_]+/i', '', $this->request['controller']);
        if ($controllerName == '') {
            $controllerName = \FLEA::getAppInf('defaultController');
        }
        if (\FLEA::getAppInf('urlLowerChar')) {
            $controllerName = strtolower($controllerName);
        }
        return $controllerName;
    }

    /**
     * 设置 Controller 名称
     *
     * @param string $controllerName 控制器名称
     *
     * @return void
     */
    public function setControllerName(string $controllerName): void
    {
        $this->request['controller'] = $controllerName;
    }

    /**
     * 获取 Action 名称
     *
     * 从请求中获取 Action 名称，如果未指定则返回配置的默认值。
     * 会自动过滤非法字符。
     *
     * @return string Action 名称
     */
    public function getActionName(): string
    {
        $actionName = preg_replace('/[^a-z0-9]+/i', '', $this->request['action']);
        if ($actionName == '') {
            $actionName = \FLEA::getAppInf('defaultAction');
        }
        return $actionName;
    }

    /**
     * 设置 Action 名称
     *
     * @param string $actionName Action 名称
     *
     * @return void
     */
    public function setActionName(string $actionName): void
    {
        $this->request['action'] = $actionName;
    }

    /**
     * 获取控制器类名
     *
     * 根据控制器名称生成对应的类名。
     * 自动添加 Controller 后缀和前缀。
     *
     * @param string $controllerName 控制器名称
     *
     * @return string 控制器类名
     */
    public function getControllerClass(string $controllerName): string
    {
        // 如果已经包含 Controller 后缀，直接使用
        if (mb_str_ends_with($controllerName, 'Controller')) {
            return \FLEA::getAppInf('controllerClassPrefix') . $controllerName;
        }

        // 将 kebab-case 转换为 PascalCase: order-apply → OrderApply
        $controllerName = kebab_to_pascal($controllerName);

        // 否则添加 Controller 后缀
        $controllerClass = \FLEA::getAppInf('controllerClassPrefix');
        if (\FLEA::getAppInf('urlLowerChar')) {
            $controllerClass .= ucfirst(strtolower($controllerName));
        } else {
            $controllerClass .= $controllerName;
        }
        return $controllerClass . 'Controller';
    }

    /**
     * 解析 URL 获取控制器和动作信息
     *
     * @param string $url URL 字符串
     *
     * @return array [控制器名，动作名，其他参数数组]
     */
    public function parseUrl(string $url): array
    {
        $url = parse_url($url);
        $args = [];
        parse_str($url['query'], $args);
        $args = array_change_key_case($args, CASE_LOWER);

        $controllerName = $args[\FLEA\Router::CONTROLLER_KEY] ?? null;
        $actionName = $args[\FLEA\Router::ACTION_KEY] ?? null;

        unset($args[\FLEA\Router::CONTROLLER_KEY]);
        unset($args[\FLEA\Router::ACTION_KEY]);
        return [$controllerName, $actionName, $args];
    }

    /**
     * 加载控制器类
     *
     * 使用 Composer PSR-4 自动加载器加载类。
     *
     * @param string $controllerClass 控制器类名
     *
     * @return bool 加载成功返回 true，失败返回 false
     */
    protected function loadController(string $controllerClass): bool
    {
        // 使用 Composer PSR-4 自动加载器加载类
        if (!class_exists($controllerClass, true)) {
            return false;
        }
        return true;
    }
}
