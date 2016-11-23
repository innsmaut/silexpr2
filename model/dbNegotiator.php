<?php

namespace MyModels;

use Doctrine\DBAL\DriverManager;

final class dbNegotiator{
    static private $conn;
    static private $table;
    static private $instance = null;

    static public function getInstance($args){
        if (self::$instance===null){
            self::$instance = new static();
            self::$conn = DriverManager::getConnection($args['doctrineConf']);
            self::$table = $args['tableName'];
        }
        return self::$instance;
    }

    private function __construct(){}
    private function __colone(){}
    private function __wakeup(){}

    public function setNew(array $args){
        self::$conn->insert(self::$table, $args);
    }

    public function deleteLink($args){
        self::$conn->delete(self::$table, $args);
    }

    public function getSelect($args = null){
        $time = date_create()->getTimestamp();
        $query = self::$conn->createQueryBuilder()->select('*')->from(self::$table)
            ->where("expired_on >= '{$time}'")->orWhere("expired_on = '0'");
        if ($args !== null){
            foreach ($args as $key => $value){
                $query->andWhere("{$key} = '{$value}'");
            }
        }
        return $query->execute()->fetchAll();
    }
}