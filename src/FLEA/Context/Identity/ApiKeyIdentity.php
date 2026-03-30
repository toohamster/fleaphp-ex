<?php

namespace FLEA\Context\Identity;

use FLEA\Context\IdentityInterface;
use FLEA\Request;

/**
 * API Key 身份标识
 *
 * 从 API Key 中获取身份标识。
 * 适用于 API 服务场景。
 *
 * @package FLEA
 * @subpackage Context\Identity
 * @author toohamster
 * @version 2.0.0
 */
class ApiKeyIdentity implements IdentityInterface
{
    /**
     * API Key 请求头名称
     *
     * @var string
     */
    private string $headerName;

    /**
     * 构造函数
     *
     * @param string $headerName 请求头名称（默认 X-API-Key）
     */
    public function __construct(string $headerName = 'X-API-Key')
    {
        $this->headerName = $headerName;
    }

    /**
     * 从 API Key 生成身份标识
     *
     * @return string API Key 的哈希值（格式：api:{hash}）
     */
    public function getId(): string
    {
        $request = Request::current();
        $apiKey = $request->getHeaderLine($this->headerName);

        if (empty($apiKey)) {
            return 'guest:' . uniqid();
        }

        // 使用 SHA256 哈希作为标识（避免暴露原始 Key）
        return 'api:' . hash('sha256', $apiKey);
    }
}
