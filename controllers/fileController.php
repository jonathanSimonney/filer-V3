<?php

namespace controllers;
use Model\FileManager;
use model\FormCheckManager;
use Model\LogManager;
use Model\NavManager;
use Model\SecurityManager;
use Model\SessionManager;
use Model\UserManager;

class fileController extends BaseController
{
    protected $fileManager;
    protected $userManager;
    protected $securityManager;
    protected $formCheckManager;
    protected $logManager;
    protected $navManager;
    protected $sessionManager;

    public function __construct(\Twig_Environment $twig, $accesslevel)
    {
        parent::__construct($twig, $accesslevel);
        $this->fileManager = FileManager::getInstance();
        $this->userManager = UserManager::getInstance();
        $this->securityManager = SecurityManager::getInstance();
        $this->formCheckManager = FormCheckManager::getInstance();
        $this->logManager = LogManager::getInstance();
        $this->navManager = NavManager::getInstance();
        $this->sessionManager = SessionManager::getInstance();
    }

    public function uploadAction(){
        $fileInformations = $this->fileManager->formatFileInfo($_FILES['file'], $_POST['name']);
        if ($this->fileManager->isUploadPossible($_FILES['file'], $fileInformations)) {
            $this->fileManager->makeUpload($_FILES['file'], $fileInformations);
            $this->logManager->writeToLog('uploaded file '.$_POST['name'].', of id '.$this->logManager->getLastInsertedId(), 'access');
        }
        header('Location: ?action=home');
        exit();
    }

    public function downloadAction(){
        $fileData = $this->fileManager->getFileData($_GET['fileId']);
        if ($this->securityManager->userCanAccess($fileData)){
            $this->fileManager->downloadFile($fileData);
            $this->logManager->writeToLog('downloaded file '.$this->fileManager->getNameWithExtent($fileData['name']).', of id '.$fileData['id'], 'access');
        }
    }

    public function replaceAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $fileData = $this->fileManager->getFileData($_POST['notForUser']);
            if ($this->securityManager->userCanAccess($fileData)){
                if ($this->fileManager->isNewFileOk($fileData)){//Did not merge these 2 if because both implement the $_SESSION['errorMessage']
                    $this->fileManager->replaceFile($this->fileManager->getRealPathToFile($fileData), $_FILES['file']);
                    $this->logManager->writeToLog('replaced file '.$this->fileManager->getNameWithExtent($fileData).', of id '.$fileData['id'].' by another file', 'access');
                }else{
                    $this->logManager->writeToLog('wanted to replace file '.$this->fileManager->getNameWithExtent($fileData).', of id '.$fileData['id'].' by a .'.$fileData['type'], 'access');
                }
            }
        }else{
            $this->logManager->writeToLog('tried to access replace page with GET request method.', 'security');
            $_SESSION['errorMessage'] = 'Please access pages with provided links, not by writing yourself url.';
        }
        header('Location: ?action=home');
        exit();
    }

    public function renameAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $fileData = $this->fileManager->getFileData($_POST['notForUser']);
            if ($this->securityManager->userCanAccess($fileData)){
                $newFileData = $this->fileManager->formatNewFileData($fileData);
                if ($this->fileManager->isNameOk($newFileData)){
                    $this->fileManager->renameFile($fileData, $newFileData);
                    $this->logManager->writeToLog('renamed file (or folder) '.$this->fileManager->getNameWithExtent($fileData).', of id '.$fileData['id'].' into '.$newFileData['name'].'.', 'access');
                }else{
                    $this->logManager->writeToLog('TRIED to rename file (or folder) '.$this->fileManager->getNameWithExtent($fileData).', of id '.$fileData['id'].' into '.$newFileData['name'].'.', 'access');
                }
            }
        }else{
            $this->logManager->writeToLog('tried to access rename page with GET request method.', 'security');
            $_SESSION['errorMessage'] = 'Please access pages with provided links, not by writing yourself url.';
        }
        header('Location: ?action=home');
        exit();
    }

    public function removeAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET'){
            $fileData = $this->fileManager->getFileData($_GET['fileId']);
            if ($this->securityManager->userCanAccess($fileData)){
                $this->fileManager->suppressFile($fileData);
                $this->logManager->writeToLog('erased file or folder '.$this->fileManager->getNameWithExtent($fileData).' of id '.$fileData['id'], 'access');
            }
        }else{
            $this->logManager->writeToLog('tried to access remove page with GET request method.', 'security');
            $_SESSION['errorMessage'] = 'Please access pages with provided links, not by writing yourself url.';
        }
        header('Location: ?action=home');
        exit();
    }

    public function addFolderAction(){
        $folderInformations = $this->fileManager->formatFolderInfo($_POST['name']);
        if ($this->fileManager->isNameOk($folderInformations)) {
            //var_dump($folderInformations);
            $this->fileManager->addFolder($folderInformations);
            $this->logManager->writeToLog('created folder '.$folderInformations['name'].', of id '.$this->logManager->getLastInsertedId(), 'access');
        }else{
            $this->logManager->writeToLog('tried to add a folder of name'.$folderInformations['name'], 'access');
        }

        header('Location: ?action=home');
        exit();
    }

    public function moveAction(){
        $movedElementData = $this->fileManager->getFileData($_GET['idMovedElement']);
        $toParent = false;
        if ($_GET['idDestination'] === 'precedent'){
            $toParent = true;

            $currentLocation = $_SESSION['location'];

            $this->navManager->closeCurrentFolder();
            $destinationId = $_SESSION['location']['simple'];

            $this->navManager->closeCurrentFolder();

            $_SESSION['location']['files']= $this->sessionManager->getItemInArray($_SESSION['location']['array'],$_SESSION);
            $destinationFolderData = $this->fileManager->getFileData($destinationId);
            $_SESSION['location'] = $currentLocation;
        }else{
            $destinationFolderData = $this->fileManager->getFileData($_GET['idDestination']);
        }
        if ($this->securityManager->userCanAccess($movedElementData) && $this->securityManager->userCanAccess($destinationFolderData, true)){
            $this->fileManager->moveFile($toParent, $movedElementData, $destinationFolderData);
            $this->logManager->writeToLog('moved file or folder '.$this->fileManager->getNameWithExtent($movedElementData).' of id '.$movedElementData['id'].' into folder '.$destinationFolderData['name'].' of id '.$destinationFolderData['id'], 'access');
        }


        header('Location: ?action=home');
        exit();
        //TODO check if destination does not have a file of same name.
    }

    public function showAction(){
        $fileData = $this->fileManager->getFileData($_GET['id']);
        http_response_code(400);

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            if ($this->securityManager->userCanAccess($fileData)){
                if (!(bool)$fileData['isFolder']){
                    http_response_code(200);

                    $ret = json_encode([
                        'path'  => '?action=show&id='.$_GET['id'],
                        'type'  => $fileData['type']
                    ]);
                    if ($ret === false){
                        echo json_last_error_msg();
                    }else{
                        echo $ret;
                    }
                }else{
                    $this->logManager->writeToLog('tried to access his folder '.$fileData['name'].' of id '.$fileData['id'], 'security');
                }
            }
        }else{
            if ($this->securityManager->userCanAccess($fileData)){
                if (!(bool)$fileData['isFolder']){
                    $path = $this->fileManager->getRealPathToFile($fileData);
                    http_response_code(200);
                    $this->fileManager->setCorrectHeader($fileData['type']);
                    readfile($path);
                    $this->logManager->writeToLog('saw his file '.$this->fileManager->getNameWithExtent($fileData).' of id '.$fileData['id'], 'access');
                }else{
                    $this->logManager->writeToLog('tried to access his folder '.$fileData['name'].' of id '.$fileData['id'], 'security');
                }
            }else{
                $this->logManager->writeToLog('tried to access file '.$this->fileManager->getNameWithExtent($fileData).' of id '.$fileData['id'].' belonging to user number '.$fileData['user_id'], 'security');
            }
        }
    }

    public function writeAction(){
        $fileData = $this->fileManager->getFileData($_GET['id']);
        http_response_code(400);
        if ($this->securityManager->userCanAccess($fileData)){
            //var_dump($_GET['newContent']);
            file_put_contents($this->fileManager->getRealPathToFile($fileData), $_GET['newContent']);
            http_response_code(200);
            $this->logManager->writeToLog('wrote into his file '.$this->fileManager->getNameWithExtent($fileData).' of id '.$fileData['id'], 'access');
            //echo file_get_contents(getRealPathToFile($fileData));
        }
    }
}