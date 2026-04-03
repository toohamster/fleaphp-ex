<?php

namespace FLEA\View;

/**
 * 流式视图接口
 *
 * 用于 SSE、实时推送等需要保持连接、持续输出的场景
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
interface StreamingViewInterface extends ViewInterface
{
    /**
     * 流式发送内容
     *
     * 此方法由 Response 调用，负责控制整个响应生命周期
     * 包括：设置响应头、循环输出、处理连接断开等
     *
     * @return void
     */
    public function stream(): void;
}
