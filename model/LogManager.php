<?php

namespace Model;

class LogManager extends BaseManager
{
    protected $dbManager;

    public function setup(){
        $this->dbManager = DbManager::getInstance();
    }

    protected function generateAccessMessage($action){

        if (!empty($_SESSION['currentUser']['data']['username'])){
            $begin = 'User '.$_SESSION['currentUser']['data']['username'];
        }else{
            $begin = 'Unknown user';
        }
        return $begin.' '.$action.' at '.date('r');
    }

    public function writeToLog($action, $file){
        if ($file === 'access'){
            $file = fopen('logs/access.log', 'ab');
        }else{
            $file = fopen('logs/security.log', 'ab');
        }
        fwrite($file, $this->generateAccessMessage($action)."\n");

        fclose($file);
    }

    public function getLastInsertedId()
    {
        return $this->dbManager->getLastInsertedId();
    }
}

