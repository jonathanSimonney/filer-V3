<?php

namespace controllers;

require_once 'model/user.php';
require_once 'model/log.php';
class userController extends BaseController
{
    public function loginAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (user_check_login($_POST)) {
                user_login($_POST['username']);
                writeToLog(generateAccessMessage('connected himself'), 'access');
            }else{
                $jsonValue = $_SESSION['errorMessage'];
                require('views/inc/json.php');
                $_SESSION['errorMessage'] = '';
            }
        }else{
            echo $this->renderView('login.html.twig', ['location' => 'login']);
            if (array_key_exists('currentUser', $_SESSION)){
                if ($_SESSION['currentUser']['loggedIn']){
                    session_destroy();
                    session_start();
                }
            }
            $_SESSION['errorMessage'] = '';
        }
    }

    public function logoutAction(){
        writeToLog(generateAccessMessage('deconnected himself'), 'access');
        session_destroy();
        header('Location: ?action=login');
        exit(0);
    }

    public function registerAction(){
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $arrayReturned = user_check_register($_POST);
            if ($arrayReturned['formOk']){
                user_register($_POST, ['username', 'email', 'password', 'indic']);
                writeToLog(generateAccessMessage('created an account as '.$_POST['username']), 'access');
            }
            $jsonValue = $arrayReturned;
            require('views/inc/json.php');
        }else{
            echo $this->renderView('register.html.twig', ['location' => 'register']);
        }
    }
}