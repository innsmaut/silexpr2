<?php

namespace MyModels;

use \Doctrine\DBAL\DriverManager;

class dbNegotiator{
    private $sql;
    private $conn;
    private $table;
    public $result;

    public function __construct($table, $connectionParams){
        $this->conn = DriverManager::getConnection($connectionParams);
        $this->table = $table;
    }

    public function getMain(){
        $this->sql = "SELECT * FROM {$this->table} 
            WHERE ((expired_on >= '{$this->timer()}') OR (expired_on = '0'))";
        return $this->conn->fetchAll($this->sql);
    }

    public function setNew(array $args){
        $this->conn->insert($this->table, $args);
    }

    public function getLinkGet($args){
        $this->sql = "SELECT * FROM {$this->table} WHERE (redirect_link = '{$args}')
            AND ((expired_on >= '{$this->timer()}') OR (expired_on = '0'))";
        return $this->conn->fetchAll($this->sql);
    }

    public function getLinkPost($args){
        $this->sql = "SELECT * FROM {$this->table} 
                WHERE (redirect_link = '{$args['link']}')
                AND (password = '{$args['password']}')
                AND ((expired_on >= '{$this->timer()}') OR (expired_on = '0'))";
        return $this->conn->fetchAll($this->sql);
    }

    private function timer(){
        return date_timestamp_get(date_create());
    }
}