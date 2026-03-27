<?php

namespace FLEA\Acl\Exception;

/**
 * \FLEA\Acl\Exception\UserGroupNotFound 指示指定的用户组没有找到
 *
 */
class UserGroupNotFound extends \FLEA\Exception
{
    public $userGroupId;

    public function __construct($userGroupId)
    {
        $this->userGroupId = $userGroupId;
        parent::__construct("UserGroup ID: {$userGroupId} not found.");
    }
}
