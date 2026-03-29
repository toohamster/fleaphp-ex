<?php
/**
 * 应用路由配置
 *
 * 在 FLEA::runMVC() 之前加载，注册的优先路由会先匹配
 */

// 文章相关路由
\FLEA\Router::get('/Post', 'PostController@index')->name('post.index');
\FLEA\Router::get('/Post/create', 'PostController@create')->name('post.create');
\FLEA\Router::get('/Post/{id:\d+}', 'PostController@show')->name('post.view');
\FLEA\Router::get('/Post/{id:\d+}/edit', 'PostController@edit')->name('post.edit');
\FLEA\Router::post('/Post/{id:\d+}/delete', 'PostController@delete')->name('post.delete');
\FLEA\Router::post('/Post/comment', 'PostController@comment')->name('post.comment');

// 兜底路由由框架自动注册
