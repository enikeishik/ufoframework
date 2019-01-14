<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

/**
 * Db wrap.
 */
class Db extends \mysqli
{
    /**
     * @var \Ufo\Core\Db
     */
    protected static $instance = null;
    
    /**
     * @var \Ufo\Core\Debug
     */
    protected $debug = null;
    
    /**
     * @var string
     */
    protected $generatedError = '';
    
    /**
     * Implementation of singleton pattern.
     * @param \Ufo\Core\DebugInterface $debug = null
     * @return \Ufo\Core\Db
     */
    public static function getInstance(DebugInterface $debug = null): self
    {
        if (null === static::$instance) {
            static::$instance = new static($debug);
        }
        
        return static::$instance;
    }
    
    /**
     * Close current connection and unset instance.
     * @return bool
     */
    public function close(): bool
    {
        static::$instance = null;
        return parent::close();
    }
    
    /**
     * @param \Ufo\Core\Debug &$debug = null
     * @throws \Ufo\Core\DbConnectException
     */
    protected function __construct(DebugInterface $debug = null)
    {
        $this->debug = $debug;
        if (null !== $this->debug) {
            $this->debug->trace(__METHOD__);
        }
        
        //подавляем вывод ошибок, т.к. иначе (даже при try-catch) выдается Warning
        @parent::__construct(C_DB_SERVER, C_DB_USER, C_DB_PASSWD, C_DB_NAME);
        if (0 != $this->connect_errno) {
            if (null !== $this->debug) {
                $this->debug->traceClose(null, $this->connect_errno, $this->connect_error);
            }
            throw new DbConnectException(preg_replace('/[^a-z0-1\s\.\-;:,_~]+/i', '', $this->connect_error));
        }
        if ('' != C_DB_CHARSET) {
            $this->query('SET NAMES ' . C_DB_CHARSET);
        }
        if (null !== $this->debug) {
            $this->debug->traceClose();
        }
    }
    
    /**
     * @see parent
     */
    public function query($query, $resultmode = null)
    {
        $query = str_replace('#__', C_DB_TABLE_PREFIX, $query);
        
        if (C_DB_READONLY 
        && 0 !== stripos($query, 'SELECT ') 
        && 0 !== stripos($query, 'SET NAMES ')) {
            if (null === $this->debug) {
                $this->generatedError = 'Readonly mode for database is on';
                return false;
            }
            $this->debug->trace($query);
            $this->debug->traceClose();
            return false;
        }
        
        if (null === $this->debug) {
            return parent::query($query);
        }
        
        $this->debug->trace($query);
        $result = parent::query($query);
        if (0 == $this->errno) {
            $this->debug->traceClose();
        } else {
            $this->debug->traceClose(null, $this->errno, $this->error);
        }
        return $result;
    }
    
    public function getItem(string $sql): ?array
    {
        $result = $this->query($sql);
        if (!$result) {
            return null;
        }
        if ($row = $result->fetch_assoc()) {
            $result->free();
            return $row;
        } else {
            $result->free();
            return null;
        }
    }
    
    public function getValue(string $sql, string $field): ?string
    {
        $item = $this->getItem($sql);
        if (is_array($item) && array_key_exists($field, $item)) {
            return $item[$field];
        } else {
            return null;
        }
    }
    
    public function getValues(string $sql, string $field, string $indexField = null): ?array
    {
        $result = $this->query($sql);
        if (!$result) {
            return null;
        }
        $items = array();
        if (is_null($indexField)) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row[$field];
            }
        } else {
            while ($row = $result->fetch_assoc()) {
                $items[$indexField . $row[$indexField]] = $row[$field];
            }
        }
        $result->free();
        return $items;
    }
    
    public function getItems(string $sql, string $indexField = null): ?array
    {
        $result = $this->query($sql);
        if (!$result) {
            return null;
        }
        $items = array();
        if (is_null($indexField)) {
            //$items = $result->fetch_all(MYSQLI_ASSOC); - use more memory (?)
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        } else {
            while ($row = $result->fetch_assoc()) {
                $items[$indexField . $row[$indexField]] = $row;
            }
        }
        $result->free();
        return $items;
    }
    
    public function getLastInsertedId()
    {
        return $this->insert_id;
    }
    
    public function addEscape(string $str): string
    {
        return $this->real_escape_string($str);
    }
    
    public function getError(): string
    {
        return '' != $this->error ? $this->error : $this->generatedError;
    }
    
    /**
     * @codeCoverageIgnore
     */
    protected function __clone()
    {
    }
    
    /**
     * @codeCoverageIgnore
     */
    private function __wakeup()
    {
    }
}
