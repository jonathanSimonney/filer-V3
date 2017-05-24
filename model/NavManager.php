<?php

namespace Model;

class NavManager extends BaseManager
{
    protected $fileManager;
    protected $securityManager;
    protected $sessionManager;

    public function setup()
    {
        $this->fileManager = FileManager::getInstance();
        $this->securityManager = SecurityManager::getInstance();
        $this->sessionManager = SessionManager::getInstance();
    }

    public function openFolder($folderId){
        $folderInformations = $this->fileManager->getFileData($folderId);
        if ($this->securityManager->userCanAccess($folderInformations)){
            array_push($_SESSION['location']['array'], $folderInformations['id'], 'childs');
            $_SESSION['location']['simple'] = $folderInformations['id'];
        }
    }

    public function closeCurrentFolder(){
        for ($i = 0;$i !== 3;$i++){
            array_pop($_SESSION['location']['array']);
        }

        $_SESSION['location']['simple'] = array_pop($_SESSION['location']['array']);

        array_push($_SESSION['location']['array'], $_SESSION['location']['simple'], 'childs');

        if ($_SESSION['location']['simple'] === null){
            $this->sessionManager->userSessionLocationInit();
        }
    }
}