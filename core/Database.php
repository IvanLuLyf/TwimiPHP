<?php

class Database
{
    private $conn;
    private static $instance;

    private function __construct()
    {
        $db_type = strtolower(constant("DB_TYPE"));
        if ($db_type == 'mysql') {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        } elseif ($db_type == 'sqlite') {
            $dsn = "sqlite:" . DB_NAME;
        }
        $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
        $this->conn = new PDO($dsn, DB_USER, DB_PASS, $option);
    }

    public static function getInstance(): Database
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function insert(array $data, $table)
    {
        $keys = implode(',', array_keys($data));
        $values = implode(',:', array_keys($data));
        $sql = "insert into {$table} ({$keys}) values(:{$values})";
        $pst = $this->conn->prepare($sql);
        foreach ($data as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst->execute();
        return $this->conn->lastInsertId();
    }

    public function update(array $data, $table, $where = null, $condition = [])
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "`{$key}` = :{$key}";
        }
        $updates = implode(',', $sets);
        $where = $where == null ? '' : ' WHERE ' . $where;
        $sql = "update {$table} set {$updates} {$where}";
        $pst = $this->conn->prepare($sql);
        foreach ($data as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        foreach ($condition as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst->execute();
        return $pst->rowCount();
    }

    public function delete($table, $where = null, $condition = [])
    {
        $where = $where == null ? '' : ' WHERE ' . $where;
        $sql = "delete from {$table} {$where}";
        $pst = $this->conn->prepare($sql);
        foreach ($condition as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst->execute();
        return $pst->rowCount();
    }

    public function fetchOne($sql, $condition = [])
    {
        $pst = $this->conn->prepare($sql);
        foreach ($condition as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst->execute();
        return $pst->fetch();
    }

    public function fetchAll($sql, $condition = [])
    {
        $pst = $this->conn->prepare($sql);
        foreach ($condition as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst->execute();
        return $pst->fetchAll();
    }

    public function createTable($tableName, $columns = [], $primary = [], $a_i = '')
    {
        $db_type = strtolower(constant("DB_TYPE"));
        if ($db_type == 'mysql') {
            $columnsData = [];
            foreach ($columns as $name => $info) {
                $columnData = $name . ' ';
                if (is_array($info)) {
                    $columnData .= implode(' ', $info);
                } else {
                    $columnData .= ' ' . $info;
                }
                if ($a_i == $name) {
                    $columnData .= ' auto_increment ';
                }
                $columnsData[] = $columnData;
            }
            $c = implode(',', $columnsData);
            $pk = '';
            if ($primary) {
                $pk .= ',primary key(' . implode(',', $primary) . ')';
            }
            $sql = "create table {$tableName}({$c}{$pk});";
            return $this->conn->exec($sql);
        } elseif ($db_type == 'sqlite') {
            $columnsData = [];
            foreach ($columns as $name => $info) {
                $columnData = $name . ' ';
                if (is_array($info)) {
                    $columnData .= implode(' ', $info);
                } else {
                    $columnData .= ' ' . $info;
                }
                if (in_array($name, $primary)) {
                    $columnData .= ' primary key ';
                }
                if ($a_i == $name) {
                    $columnData .= ' autoincrement ';
                }
                $columnsData[] = $columnData;
            }
            $c = implode(',', $columnsData);
            $sql = "create table {$tableName}({$c});";
            return $this->conn->exec($sql);
        } else {
            return -1;
        }
    }
}