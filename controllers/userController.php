<?php

namespace controllers;

use Model\LogManager;
use Model\UserManager;

class userController extends BaseController
{

    protected $userManager;
    protected $logManager;

    public function __construct(\Twig_Environment $twig, $accesslevel)
    {
        parent::__construct($twig, $accesslevel);
        $this->userManager = UserManager::getInstance();
        $this->logManager = LogManager::getInstance();
    }

    public function loginAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->userManager->userCheckLogin($_POST)) {
                $this->userManager->userLogin($_POST['username']);
                $this->logManager->writeToLog('connected himself', 'access');
            }else{
                echo json_encode($_SESSION['errorMessage']);
                $_SESSION['errorMessage'] = '';
            }
        }else{
            echo $this->renderView('login.html.twig', ['location' => 'login']);
            if (isset($_SESSION['currentUser']) && isset($_SESSION['currentUser']['loggedIn']) && $_SESSION['currentUser']['loggedIn']){
                session_destroy();
                session_start();
            }
            $_SESSION['errorMessage'] = '';
        }
    }

    public function logoutAction(){
        $this->logManager->writeToLog('deconnected himself', 'access');
        session_destroy();
        header('Location: ?action=login');
        exit(0);
    }

    public function registerAction(){
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $arrayReturned = $this->userManager->userCheckRegister($_POST);
            if ($arrayReturned['formOk']){
                $this->userManager->userRegister($_POST, ['username', 'email', 'password', 'indic']);
                $this->logManager->writeToLog('created an account as '.$_POST['username'], 'access');
            }
            echo json_encode($arrayReturned);
        }else{
            echo $this->renderView('register.html.twig', ['location' => 'register']);
        }
    }
}