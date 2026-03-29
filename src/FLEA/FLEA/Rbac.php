<?php

namespace FLEA;

/**
 * \FLEA\Rbac 提供基于角色的权限检查服务
 *
 * \FLEA\Rbac 并不提供用户管理和角色管理服务，
 * 这些服务由 \FLEA\Rbac_UsersManager 和 \FLEA\Rbac_RolesManager 提供。
 */
class Rbac
{
    /**
     * 指示在 session 中用什么名字保存用户的信息
     *
     * @var string
     */
    public string $sessionKey = 'RBAC_USERDATA';

    /**
     * 指示用户数据中，以什么键保存角色信息
     *
     * @var string
     */
    public string $rolesKey = 'RBAC_ROLES';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->sessionKey = \FLEA::getAppInf('RBACSessionKey');
        if ($this->sessionKey == 'RBAC_USERDATA') {
            trigger_error(_ET(0x0701005), E_USER_WARNING);
        }
    }

    /**
     * 将用户数据保存到 session 中
     *
     * @param array $userData
     * @param mixed $rolesData
     */
    public function setUser(array $userData, $rolesData = null): void
    {
        if ($rolesData) {
            $userData[$this->rolesKey] = $rolesData;
        }
        $_SESSION[$this->sessionKey] = $userData;
    }

    /**
     * 获取保存在 session 中的用户数据
     *
     * @return array
     */
    public function getUser(): ?array
    {
        return $_SESSION[$this->sessionKey] ?? null;
    }

    /**
     * 从 session 中清除用户数据
     */
    public function clearUser(): void
    {
        unset($_SESSION[$this->sessionKey]);
    }

    /**
     * 获取 session 中用户信息包含的角色
     *
     * @return mixed
     */
    public function getRoles()
    {
        $user = $this->getUser();
        return $user[$this->rolesKey] ?? null;
    }

    /**
     * 以数组形式返回用户的角色信息
     *
     * @return array
     */
    public function getRolesArray(): array
    {
        $roles = $this->getRoles();
        if (is_array($roles)) { return $roles; }
        $tmp = array_map('trim', explode(',', $roles));
        return array_filter($tmp, 'trim');
    }

    /**
     * 检查访问控制表是否允许指定的角色访问
     *
     * @param array $roles
     * @param array $ACT
     *
     * @return boolean
     */
    public function check(array &$roles, array &$ACT): bool
    {
        $roles = array_map('strtoupper', $roles);
        if ($ACT['allow'] == RBAC_EVERYONE) {
            // 如果 allow 允许所有角色，deny 没有设置，则检查通过
            if ($ACT['deny'] == RBAC_NULL) { return true; }
            // 如果 deny 为 RBAC_NO_ROLE，则只要用户具有角色就检查通过
            if ($ACT['deny'] == RBAC_NO_ROLE) {
                if (empty($roles)) { return false; }
                return true;
            }
            // 如果 deny 为 RBAC_HAS_ROLE，则只有用户没有角色信息时才检查通过
            if ($ACT['deny'] == RBAC_HAS_ROLE) {
                if (empty($roles)) { return true; }
                return false;
            }
            // 如果 deny 也为 RBAC_EVERYONE，则表示 ACT 出现了冲突
            if ($ACT['deny'] == RBAC_EVERYONE) {
                throw new \FLEA\Rbac\Exception\InvalidACT($ACT);
            }

            // 只有 deny 中没有用户的角色信息，则检查通过
            foreach ($roles as $role) {
                if (in_array($role, $ACT['deny'], true)) { return false; }
            }
            return true;
        }

        do {
            // 如果 allow 要求用户具有角色，但用户没有角色时直接不通过检查
            if ($ACT['allow'] == RBAC_HAS_ROLE) {
                if (!empty($roles)) { break; }
                return false;
            }

            // 如果 allow 要求用户没有角色，但用户有角色时直接不通过检查
            if ($ACT['allow'] == RBAC_NO_ROLE) {
                if (empty($roles)) { break; }
                return false;
            }

            if ($ACT['allow'] != RBAC_NULL) {
                // 如果 allow 要求用户具有特定角色，则进行检查
                $passed = false;
                foreach ($roles as $role) {
                    if (in_array($role, $ACT['allow'], true)) {
                        $passed = true;
                        break;
                    }
                }
                if (!$passed) { return false; }
            }
        } while (false);

        // 如果 deny 没有设置，则检查通过
        if ($ACT['deny'] == RBAC_NULL) { return true; }
        // 如果 deny 为 RBAC_NO_ROLE，则只要用户具有角色就检查通过
        if ($ACT['deny'] == RBAC_NO_ROLE) {
            if (empty($roles)) { return false; }
            return true;
        }
        // 如果 deny 为 RBAC_HAS_ROLE，则只有用户没有角色信息时才检查通过
        if ($ACT['deny'] == RBAC_HAS_ROLE) {
            if (empty($roles)) { return true; }
            return false;
        }
        // 如果 deny 为 RBAC_EVERYONE，则检查失败
        if ($ACT['deny'] == RBAC_EVERYONE) {
            return false;
        }

        // 只有 deny 中没有用户的角色信息，则检查通过
        foreach ($roles as $role) {
            if (in_array($role, $ACT['deny'], true)) { return false; }
        }
        return true;
    }

    /**
     * 对原始 ACT 进行分析和整理，返回整理结果
     *
     * @param array $ACT
     *
     * @return array
     */
    public function prepareACT(array $ACT): array
    {
        $ret = [];
        $arr = ['allow', 'deny'];
        foreach ($arr as $key) {
            do {
                if (!isset($ACT[$key])) {
                    $value = RBAC_NULL;
                    break;
                }

                if ($ACT[$key] == RBAC_EVERYONE || $ACT[$key] == RBAC_HAS_ROLE
                    || $ACT[$key] == RBAC_NO_ROLE || $ACT[$key] == RBAC_NULL) {
                    $value = $ACT[$key];
                    break;
                }

                $value = explode(',', strtoupper($ACT[$key]));
                $value = array_filter(array_map('trim', $value), 'trim');
                if (empty($value)) { $value = RBAC_NULL; }
            } while (false);
            $ret[$key] = $value;
        }

        return $ret;
    }
}
