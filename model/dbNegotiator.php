<?php

namespace MyModels;

use Doctrine\DBAL\DriverManager;

class dbNegotiator{
    private $conn;
    private $table;

    public function __construct($table, $connectionParams){
        $this->conn = DriverManager::getConnection($connectionParams);
        $this->table = $table;
    }

    public function setNew(array $args){
        $this->conn->insert($this->table, $args);
    }

    public function deleteLink($args){
        $this->conn->delete($this->table, $args);
    }

    public function getSelect($args = null){
        $time = date_create()->getTimestamp();
        $query = $this->conn->createQueryBuilder()->select('*')->from($this->table)
            ->where("expired_on >= '{$time}'")->orWhere("expired_on = '0'");
        if ($args !== null){
            foreach ($args as $key => $value){
                
                $query->andWhere("{$key} = '{$value}'");
            }
        }
        return $query->execute()->fetchAll();
    }
}