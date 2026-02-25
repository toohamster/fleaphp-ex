<?php



namespace FLEA\Acl\Exception;
/**
 * 定义 \FLEA\Acl_Exception_UserGroupNotFound 异常
 *
 * @author toohamster
 * @package Core
 * @version $Id: UserGroupNotFound.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */

/**
 * \FLEA\Acl_Exception_UserGroupNotFound 指示指定的用户组没有找到
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class UserGroupNotFound extends \FLEA\Exception
{
    public $userGroupId;

    function __construct($userGroupId)
    {
        $this->userGroupId = $userGroupId;
        parent::__construct("UserGroup ID: {$userGroupId} not found.");
    }
}
