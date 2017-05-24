<?php

namespace Model;

class UserManager extends BaseManager
{
    protected $dbManager;
    protected $sessionManager;
    protected $formCheckManager;
    protected $logManager;

    public function setup()
    {
        $this->dbManager = DbManager::getInstance();
        $this->sessionManager = SessionManager::getInstance();
        $this->formCheckManager = FormCheckManager::getInstance();
        $this->logManager = LogManager::getInstance();
    }

    protected function getUserByUsername($username){
        $data = $this->dbManager->findOneSecure('SELECT * FROM users WHERE username = :username',
            ['username' => $username]);
        return $data;
    }

    protected function userHash($pass){
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        return $hash;
    }

    protected function transformData($data){
        $data['password'] = $this->userHash($data['password']);
        return $data;
    }

    public function getUserById($id){
        $id = (int)$id;
        $data = $this->dbManager->findOne('SELECT * FROM users WHERE id = '.$id);
        return $data;
    }

    public function userCheckRegister($data){
        $_SESSION['errorMessage'] ='';

        $this->formCheckManager->checkRequiredFiels(['username', 'email', 'password', 'confirmationOfPassword', 'indic']);
        $this->formCheckManager->checkUniqFiel(['username' => 'users', 'email' => 'users']);

        $this->formCheckManager->checkEmail($_POST['email']);
        $this->formCheckManager->checkPassword($_POST['password'], $_POST['confirmationOfPassword']);

        return $this->formCheckManager->getArrayReturned($_SESSION['errorMessage']);
    }

    public function userRegister($data, $arrayFields){
        $data = $this->transformData($data);//currently useless (function with only one instruction... But allows easier improvement if in the future one want to add other
        // transformation to data before inscription in db.
        foreach ($arrayFields as $field) {
            $user[$field] = $data[$field];
        }
        $this->dbManager->dbInsert('users', $user);
        $user = $this->dbManager->getWhatHow($data['username'], 'username', 'users')[0];
        $_SESSION['currentUser']['data'] = $user;//currently useless, but could be used later to pre-fill login field or something else.
        $_SESSION['currentUser']['loggedIn'] = false;

        mkdir('uploads/'.$user['id']);//create folder for user file
    }

    public function userCheckLogin($data){
        $_SESSION['errorMessage'] = '';
        if (empty($data['username']) OR empty($data['password'])){
            $_SESSION['errorMessage'] = 'The fields username and password are required.';
            return false;
        }
        $user = $this->getUserByUsername($data['username']);
        if ($user === false){
            $_SESSION['errorMessage'] = 'Sorry, but the username '.$data['username'].' is not attributed. Try to type another username.';
            return false;
        }

        if (password_verify($data['password'], $user['password'])) {
            return true;
        }
        $_SESSION['errorMessage'] = 'Sorry, but your password does not correspond to your username. Try to take into account the following : '.htmlspecialchars($user['indic']);
        $this->logManager->writeToLog('tried to connect as '.$data['username'], 'security');
        return false;
    }

    public function userLogin($username){
        $data = $this->getUserByUsername($username);
        $_SESSION['currentUser']['data'] = $data;
        //var_dump($_SESSION['files']);
        $_SESSION['files'] = $this->dbManager->getWhatHow($_SESSION['currentUser']['data']['id'],'user_id','files');
        $_SESSION['files'] = $this->makeInferiorKeyIndex($_SESSION['files'], 'id');
        //var_dump($_SESSION['files']);
        $this->sessionManager->formatSessionFileAsTree();
        //var_dump($_SESSION['files']);
        $_SESSION['currentUser']['loggedIn'] = true;
        $this->sessionManager->userSessionLocationInit();
    }

}