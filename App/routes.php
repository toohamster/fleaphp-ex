<?php
/**
 * 应用路由配置
 *
 * 在 FLEA::runMVC() 之前加载，注册的优先路由会先匹配
 */

// 首页路由 - 根路径和 index.php 都指向 PostController 的 index 动作
\FLEA\Router::get('/', 'PostController@index');
\FLEA\Router::get('/index.php', 'PostController@index');

// 文章相关路由（可选，兜底路由已覆盖）
// \FLEA\Router::get('/post', 'PostController@index');
// \FLEA\Router::get('/post/view/{id:\d+}', 'PostController@show');
