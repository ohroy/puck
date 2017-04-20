<?php

namespace puck;

/**
 * @property  totalCount
 */
class Db {
    protected $db;
    protected $field;
    protected $table;
    protected $limitCount = null;

    private $connPool;

    public function connect($conn = null) {
        if ($conn === null) {
            $conn = 'db.main';
        }
        $dbConf = [];
        if (is_string($conn)) {
            $dbConf = app('config')->get($conn);
        } elseif (is_array($conn)) {
            $dbConf = $conn;
        } else {
            throw new \InvalidArgumentException("数据库配置必须为字符串或者数组");
        }
        if (is_null($dbConf)) {
            throw new \InvalidArgumentException("数据库配置异常！");
        }
        $confMd5 = md5(serialize($dbConf));
        if (!isset($this->connPool[$confMd5])) {
            $obj = new Mysql($dbConf);
            $this->connPool[$confMd5] = $obj;
        }
        return $this->connPool[$confMd5];
    }


    public function __call($method, $arg) {
        $ret = $this;
        if (method_exists($this->db, $method)) {
            $ret = call_user_func_array(array($this->db, $method), $arg);
        }
        return $ret == $this->db ? $this : $ret;
    }

    public function __get($name) {
        if (property_exists($this->db, $name)) {
            return $this->db->$name;
        }
        throw new MemberAccessException('model Property ' . $name . ' not exists');
    }
}