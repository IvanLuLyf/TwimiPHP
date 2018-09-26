<?php

class Model
{
    protected $_table;
    protected $_name;
    private $_filter = '';
    private $_join = '';
    private $_param = [];

    public function __construct()
    {
        if (!$this->_table) {
            $this->_name = substr(get_class($this), 0, -5);
            $this->_table = DB_PREFIX . strtolower($this->_name);
        }
    }

    private function reset()
    {
        $this->_filter = '';
        $this->_join = '';
        $this->_param = [];
    }

    public function where($where, $param = [])
    {
        if ($where) {
            $this->_filter .= ' where ';
            if (is_array($where)) {
                $this->_filter .= implode(' ', $where);
            } else {
                $this->_filter .= $where;
            }
            $this->_param = $param;
        }
        return $this;
    }

    public function join($tableName, $condition = [], $mod = '')
    {
        $this->_join .= " $mod join `$tableName`";
        if (is_array($condition)) {
            $this->_join .= " on (" . implode(' ', $condition) . ") ";
        } else {
            $this->_join .= " on ($condition) ";
        }
        return $this;
    }

    public function limit($size, $start = 0)
    {
        $this->_filter .= " limit $start,$size ";
        return $this;
    }

    public function order($order = [])
    {
        if (is_array($order)) {
            $this->_filter .= ' order by ';
            $this->_filter .= implode(',', $order);
        } else {
            $this->_filter .= " order by $order ";
        }
        return $this;
    }

    public function fetch($column = '*')
    {
        if (is_array($column)) {
            $column = implode(',', $column);
        }
        $sql = "select {$column} from `{$this->_table}` {$this->_join} {$this->_filter}";
        $result = Database::getInstance()->fetchOne($sql, $this->_param);
        $this->reset();
        return $result;
    }

    public function fetchAll($column = '*')
    {
        if (is_array($column)) {
            $column = implode(',', $column);
        }
        $sql = "select {$column} from `{$this->_table}` {$this->_join} {$this->_filter}";
        $result = Database::getInstance()->fetchAll($sql, $this->_param);
        $this->reset();
        return $result;
    }

    public function delete()
    {
        $result = Database::getInstance()->delete($this->_table, $this->_filter, $this->_param);
        $this->reset();
        return $result;
    }

    public function add($data = [])
    {
        Database::getInstance()->insert($data, $this->_table);
    }

    public function update($data = [])
    {
        $result = Database::getInstance()->update($data, $this->_table, $this->_filter, $this->_param);
        $this->reset();
        return $result;
    }
}