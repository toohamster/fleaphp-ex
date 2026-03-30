<?php

namespace FLEA\Acl\Exception;

/**
 * UserGroupNotFound 异常
 *
 * 指示指定的用户组没有找到。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class UserGroupNotFound extends \FLEA\Exception
{
    /**
     * @var mixed 用户组 ID
     */
    public $userGroupId;

    /**
     * 构造函数
     *
     * @param mixed $userGroupId 用户组 ID
     */
    public function __construct($userGroupId)
    {
        $this->userGroupId = $userGroupId;
        parent::__construct("UserGroup ID: {$userGroupId} not found.");
    }
}
