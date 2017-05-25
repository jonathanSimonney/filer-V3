<?php
namespace controllers;

use Model\DbManager;
use Model\FileManager;
use Model\SecurityManager;
use Model\SessionManager;
use Model\UserManager;

class defaultController extends BaseController
{
    protected $dbManager;
    protected $userManager;
    protected $securityManager;
    protected $sessionManager;
    protected $fileManager;

    public function __construct(\Twig_Environment $twig, $accessLevel)
    {
        parent::__construct($twig, $accessLevel);
        $this->dbManager = DbManager::getInstance();
        $this->sessionManager = SessionManager::getInstance();
        $this->securityManager = SecurityManager::getInstance();
        $this->userManager = UserManager::getInstance();
        $this->fileManager = FileManager::getInstance();
    }

    public function homeAction(){
        $_SESSION['location']['files'] = $this->sessionManager->getItemInArray($_SESSION['location']['array'],$_SESSION) ?? [];
        $arrayElements = $_SESSION['location']['files'];

        //var_dump($arrayElements, $_SESSION['location'], $_SESSION['files']);


        if ($arrayElements !== null){

            $arrayElements = $this->fileManager->orderBetweenFilesAndFolder($arrayElements);
        }
        else
        {
            $arrayElements = [];
        }

        echo $this->renderView('home.html.twig', ['errorMessage' => $_SESSION['errorMessage'], 'tree' => $_SESSION['files'], 'location' => $_SESSION['location'], 'arrayElement' => $arrayElements, 'currentUser' => $_SESSION['currentUser']['data']['username']]);



        /*if ($arrayElements !== null){
            $numberForId = 0;

            $arrayElements = orderBetweenFilesAndFolder($arrayElements);

            foreach ($arrayElements as $key => $value){
                //var_dump(getRealPathToFile($value));
                $numberForId++;
                if ($value['isFolder']){
                    require 'views/inc/folder.html.twig';
                }else{
                    require 'views/inc/file.html.twig';
                }
            }
        }*/
        $_SESSION['errorMessage'] = '';
    }

    public function welcomeAction()
    {
        if ($this->getConnectionStatus() === 'connected')
        {
            header('Location: ?action=home');
            exit(0);
        }

        header('Location: ?action=login');
        exit(0);
    }
}