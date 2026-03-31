<?php
/**
 * 应用路由配置
 *
 * 在 FLEA::runMVC() 之前加载，注册的优先路由会先匹配
 */

// 文章相关路由
\FLEA\Router::get('/post', 'PostController@index')->name('post.index');
\FLEA\Router::get('/post/create', 'PostController@create')->name('post.create');
\FLEA\Router::get('/post/{id:\d+}', 'PostController@view')->name('post.view');
\FLEA\Router::any('/post/{id:\d+}/edit', 'PostController@edit')->name('post.edit');
\FLEA\Router::post('/post/{id:\d+}/delete', 'PostController@delete')->name('post.delete');
\FLEA\Router::post('/post/comment', 'PostController@comment')->name('post.comment');

// 兜底路由由框架自动注册
