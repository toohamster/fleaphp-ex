<?php

namespace FLEA\Context\Identity;

use FLEA\Context\IdentityInterface;
use FLEA\Request;

/**
 * 请求 ID 身份标识
 *
 * 使用请求 ID（X-Request-ID）或生成的唯一 ID 作为身份标识。
 * 适用于无状态服务、日志追踪场景。
 *
 * @package FLEA
 * @subpackage Context\Identity
 * @author toohamster
 * @version 2.0.0
 */
class RequestIdIdentity implements IdentityInterface
{
    /**
     * 请求 ID 请求头名称
     *
     * @var string
     */
    private string $headerName;

    /**
     * 构造函数
     *
     * @param string $headerName 请求头名称（默认 X-Request-ID）
     */
    public function __construct(string $headerName = 'X-Request-ID')
    {
        $this->headerName = $headerName;
    }

    /**
     * 获取请求 ID
     *
     * @return string 请求 ID（格式：req:{id}）
     */
    public function getId(): string
    {
        $request = Request::current();
        $requestId = $request->getHeaderLine($this->headerName);

        if (empty($requestId)) {
            // 自动生成唯一 ID
            $requestId = uniqid('req_', true);
        }

        return 'req:' . $requestId;
    }
}
