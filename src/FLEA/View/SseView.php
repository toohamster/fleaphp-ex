<?php

namespace FLEA\View;

/**
 * SSE (Server-Sent Events) 流式视图
 *
 * 用于实时推送场景，保持连接持续输出
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class SseView implements StreamingViewInterface
{
    /**
     * @var callable Generator 函数，yield 数据
     */
    private $generator;

    /**
     * 构造函数
     *
     * @param callable $generator Generator 函数
     */
    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }

    /**
     * 获取内容类型
     *
     * @return string
     */
    public function getContentType(): string
    {
        return 'text/event-stream';
    }

    /**
     * 获取内容（流式视图无完整内容）
     *
     * @return string
     */
    public function getContent(): string
    {
        return '';
    }

    /**
     * 流式发送内容
     *
     * @return void
     */
    public function stream(): void
    {
        // 禁用缓冲
        if (ob_get_level()) {
            ob_end_clean();
        }

        // SSE 响应头
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');  // Nginx
        header('Content-Encoding: none');

        // 调用数据生成器
        $generator = $this->generator;
        foreach ($generator() as $data) {
            echo "data: " . json_encode($data) . "\n\n";
            flush();

            // 检查客户端是否断开
            if (connection_aborted()) {
                break;
            }
        }
    }
}
