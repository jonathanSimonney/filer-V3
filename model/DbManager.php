<?php

namespace Model;

use \PDO;
use \PDOException;

class DbManager extends BaseManager
{
    protected $sessionManager;
    protected $dbh;

    public function setup()
    {
        $this->sessionManager = SessionManager::getInstance();
    }

    protected function connectToDb(){
        global $privateConfig;
        $db_config = $privateConfig['db_config'];
        $dsn = 'mysql:dbname='.$db_config['name'].';host='.$db_config['host'];
        $user = $db_config['user'];
        $password = $db_config['pass'];

        try {
            $dbh = new PDO($dsn, $user, $password);
        }

        catch (PDOException $e){
            echo 'Connexion échouée : ' . $e->getMessage();
        }

        return $dbh;
    }

    protected function getDbh(){
        global $dbh;
        if ($dbh === null){
            $dbh = $this->connectToDb();
        }
        return $dbh;
    }

    public function dbInsert($table, $data = [], $keyCorrespond = false){
        $dbh = $this->getDbh();
        $query = 'INSERT INTO `' . $table . '` VALUES (NULL,';
        $keyQuery = '(`id`';
        $first = true;
        foreach ($data AS $k => $value) {
            if (!$first){
                $query .= ', ';
            }else{
                $first = false;
            }
            $query .= ':'.$k;
            $keyQuery .= ',`'.$k.'`';
        }
        $query .= ')';
        $keyQuery .= ')';

        if ($keyCorrespond){
            $query = str_replace('INSERT INTO `' . $table . '` VALUES', 'INSERT INTO `' . $table . '`'.$keyQuery.' VALUES', $query);
        }
        $sth = $dbh->prepare($query);
        $sth->execute($data);
        return true;
    }

    public function findOne($query){
        $dbh = $this->getDbh();
        $data = $dbh->query($query, PDO::FETCH_ASSOC);
        $result = $data->fetch();
        return $result;
    }

    public function findOneSecure($query, $data = []){
        $dbh = $this->getDbh();
        $sth = $dbh->prepare($query);
        $sth->execute($data);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function findAll($query){
        $dbh = $this->getDbh();
        $data = $dbh->query($query, PDO::FETCH_ASSOC);
        $result = $data->fetchAll();
        return $result;
    }

    public function findAllSecure($query, $data = []){
        $dbh = $this->getDbh();
        $sth = $dbh->prepare($query);
        $sth->execute($data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getLastInsertedId(){
        $dbh = $this->getDbh();
        return $dbh->lastInsertId();
    }

    public function getWhatHow($needle, $needleColumn, $needleTable){
        $data = $this->findAllSecure('SELECT * FROM `'.$needleTable.'` WHERE `'.$needleColumn.'` = :needle',
            ['needle' => $needle]);

        return $data;
    }

    public function dbUpdate($table, $id, $fieldToUpdateData){
        $dbh = $this->getDbh();
        $first = true;
        $query = 'UPDATE `' . $table . '` SET ';
        foreach ($fieldToUpdateData AS $key => $value){
            if (!$first){
                $query .= ', ';
            }else{
                $first = false;
            }

            $query .= '`'.$key.'` =:'.$key;
        }

        $query .= ' WHERE `'.$table.'`.`id` = '.$id;

        /*echo $query;
        var_dump($fieldToUpdateData);*/
        $sth = $dbh->prepare($query);
        $sth->execute($fieldToUpdateData);
    }

    public function removeFromDb($table, $id){
        $dbh = $this->getDbh();
        $query = 'DELETE FROM `'.$table.'` WHERE `'.$table.'`.`id` = '.$id.';';
        $sth = $dbh->prepare($query);
        $sth->execute();
    }
}