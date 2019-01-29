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
     * @var \Ufo\Core\ConfigInterface
     */
    protected $config = null;
    
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
     * @param \Ufo\Core\ConfigInterface $config
     * @param \Ufo\Core\DebugInterface $debug = null
     * @return \Ufo\Core\Db
     */
    public static function getInstance(ConfigInterface $config, DebugInterface $debug = null): self
    {
        if (null === static::$instance) {
            static::$instance = new static($config, $debug);
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
    protected function __construct(ConfigInterface $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        if (null !== $this->debug) {
            $this->debug->trace(__METHOD__);
        }
        
        //suppressing errors, because (even in try-catch) Warning will showed
        @parent::__construct(
            $this->config->dbServer, 
            $this->config->dbUser, 
            $this->config->dbPassword, 
            $this->config->dbName
        );
        if (0 != $this->connect_errno) {
            if (null !== $this->debug) {
                $this->debug->traceClose(null, $this->connect_errno, $this->connect_error);
            }
            throw new DbConnectException(preg_replace('/[^a-z0-1\s\.\-;:,_~]+/i', '', $this->connect_error));
        }
        if ('' != $this->config->dbCharset) {
            $this->query('SET NAMES ' . $this->config->dbCharset);
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
        $query = str_replace('#__', $this->config->dbTablePrefix, $query);
        
        if ($this->config->dbReadonly 
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
    
    /**
     * @param string $sql
     * @return ?array
     */
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
    
    /**
     * @param string $sql
     * @param string $objectType = 'stdClass'
     * @return ?object
     */
    public function getItemObject(string $sql, string $objectType = 'stdClass'): ?object
    {
        $result = $this->query($sql);
        if (!$result) {
            return null;
        }
        if ($row = $result->fetch_object($objectType)) {
            $result->free();
            return $row;
        } else {
            $result->free();
            return null;
        }
    }
    
    /**
     * @param string $sql
     * @param string $field
     * @return ?string
     */
    public function getValue(string $sql, string $field): ?string
    {
        $item = $this->getItem($sql);
        if (is_array($item) && array_key_exists($field, $item)) {
            return $item[$field];
        } else {
            return null;
        }
    }
    
    /**
     * @param string $sql
     * @param string $field
     * @param string $indexField = null
     * @return ?array
     */
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
    
    /**
     * @param string $sql
     * @param string $indexField = null
     * @return ?array
     */
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
    
    /**
     * @param string $sql
     * @param string $objectType = 'stdClass'
     * @return ?array
     */
    public function getItemsObjects(string $sql, string $objectType = 'stdClass'): ?array
    {
        $result = $this->query($sql);
        if (!$result) {
            return null;
        }
        $items = array();
        while ($row = $result->fetch_object($objectType)) {
            $items[] = $row;
        }
        $result->free();
        return $items;
    }
    
    /**
     * @return int|string
     */
    public function getLastInsertedId()
    {
        return $this->insert_id;
    }
    
    /**
     * @param string $str
     * @return string
     */
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
