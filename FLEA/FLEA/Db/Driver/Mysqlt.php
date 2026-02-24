<?php

namespace FLEA\Db\Driver;

/**
 * Transaction-enabled MySQL driver
 *
 * @package Core
 * @author toohamster
 * @version 2.0
 */
class Mysqlt extends \FLEA\Db\Driver\Mysql
{
    /**
     * @var bool
     */
    public $HAS_TRANSACTION = true;

    /**
     * Connect and enable savepoint support
     *
     * @param array|false $dsn
     * @return bool
     */
    public function connect($dsn = false): bool
    {
        parent::connect($dsn);
        return true;
    }

    /**
     * Start transaction
     *
     * @return bool
     */
    protected function _startTrans(): bool
    {
        try {
            return $this->pdo->beginTransaction() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Complete transaction
     *
     * @param bool $commitOnNoErrors
     * @return bool
     */
    protected function _completeTrans(bool $commitOnNoErrors = true): bool
    {
        try {
            if ($this->_hasFailedQuery == false && $commitOnNoErrors) {
                return $this->pdo->commit() !== false;
            } else {
                return $this->pdo->rollBack() !== false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}
