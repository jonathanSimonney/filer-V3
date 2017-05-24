<?php

namespace Model;

class SecurityManager extends BaseManager
{
    protected $logManager;

    public function setup()
    {
        $this->logManager = LogManager::getInstance();
    }

    public function userCanAccess($fileData, $canBeRoot = false){
        if ($fileData['name'] === 'root' && $canBeRoot){
            return true;
        }

        if($fileData['user_id'] === $_SESSION['currentUser']['data']['id']){
            return true;
        }

        $this->logManager->writeToLog('tried to access folder '.$fileData['name'].' of id '.$fileData['id'].' belonging to user number'.$fileData['user_id'], 'security');
        $_SESSION['errorMessage'] = 'You tried to access a file which wasn\'t one of your files.';
        return false;
    }
}