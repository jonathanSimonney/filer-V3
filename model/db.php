<?php
require_once 'model/session.php';

$dbh = null;

function connect_to_db(){
    global $db_config;
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

function get_dbh(){
    global $dbh;
    if ($dbh === null){
        $dbh = connect_to_db();
    }
    return $dbh;
}

function db_insert($table, $data = [], $keyCorrespond = false){
    $dbh = get_dbh();
    $query = 'INSERT INTO `' . $table . '` VALUES ("",';
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

function find_one($query){
    $dbh = get_dbh();
    $data = $dbh->query($query, PDO::FETCH_ASSOC);
    $result = $data->fetch();
    return $result;
}

function find_one_secure($query, $data = []){
    $dbh = get_dbh();
    $sth = $dbh->prepare($query);
    $sth->execute($data);
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function find_all($query){
    $dbh = get_dbh();
    $data = $dbh->query($query, PDO::FETCH_ASSOC);
    $result = $data->fetchAll();
    return $result;
}

function find_all_secure($query, $data = []){
    $dbh = get_dbh();
    $sth = $dbh->prepare($query);
    $sth->execute($data);
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function get_last_inserted_id(){
    $dbh = get_dbh();
    return $dbh->lastInsertId();
}

function get_what_how($needle, $needleColumn, $needleTable){
    $data = find_all_secure('SELECT * FROM `'.$needleTable.'` WHERE `'.$needleColumn.'` = :needle',
            ['needle' => $needle]);

    return $data;
}

function db_update($table, $id, $fieldToUpdateData){
    $dbh = get_dbh();
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

function db_suppress($table, $id){
    $dbh = get_dbh();
    $query = 'DELETE FROM `'.$table.'` WHERE `'.$table.'`.`id` = '.$id.';';
    $sth = $dbh->prepare($query);
    $sth->execute();
}