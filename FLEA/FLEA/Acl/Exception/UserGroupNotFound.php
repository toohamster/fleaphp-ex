<?php


/**
 * 定义 FLEA_Acl_Exception_UserGroupNotFound 异常
 *
 * @author toohamster
 * @package Core
 * @version $Id: UserGroupNotFound.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */

/**
 * FLEA_Acl_Exception_UserGroupNotFound 指示指定的用户组没有找到
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Acl_Exception_UserGroupNotFound extends FLEA_Exception
{
    public $userGroupId;

    function FLEA_Acl_Exception_UserGroupNotFound($userGroupId)
    {
        $this->userGroupId = $userGroupId;
        parent::FLEA_Exception("UserGroup ID: {$userGroupId} not found.");
    }
}
