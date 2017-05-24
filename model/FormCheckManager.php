<?php

namespace model;

class FormCheckManager extends BaseManager
{
    protected $dbManager;

    public function setup()
    {
        $this->dbManager = DbManager::getInstance();
    }

    protected function requiredField($name){
        $noError = false;

        if (array_key_exists($name, $_POST)) {
            if ($_POST[$name] !== '') {
                $noError = true;
            }
        }

        if (!$noError) {
            if (preg_match("/[A-Z]{1}/", $name)===1) {
                $name = preg_replace("/([A-Z])/", " $1", $name);
                $name = strtolower($name);
            }
            $_SESSION['errorMessage'] .= "The field ".$name." is required and you didn't fill it.<br>";
            return false;
        }

        return true;
    }

    protected function isNotAlreadyInDb($needle, $column, $table){
        if (getWhatHow($needle, $column, $table)){
            $_SESSION['errorMessage'] .= "Sorry, but the ".$column." <i>".htmlspecialchars($needle)."</i> is already taken, please choose another one.<br>";
        }
    }

    public function checkRequiredFiels($arrayRequiredField){
        foreach ($arrayRequiredField as $item) {
            $this->requiredField($item);
        }
    }

    public function checkUniqFiel($objectUniqField){
        foreach ($objectUniqField as $item => $table) {
            $this->isNotAlreadyInDb($_POST[$item], $item, $table);
        }
    }

    public function checkEmail($potentialEmail){
        $re = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
        if (preg_match($re, $potentialEmail) !== 1) {
            $_SESSION['errorMessage'] .= 'Your email address is invalid.<br>';
        }
    }

    public function checkPassword($password, $confirmationPassword){
        if ($password !== $confirmationPassword) {
            $_SESSION['errorMessage'] .= 'You must write the same thing in the fields password and confirmation of password<br>';
        }//BONUS : add a security level of password

        if (strlen($password) <= 8) {
            $_SESSION['errorMessage'] .= 'your password must do at least 8 characters';
        }
    }

    public function getArrayReturned($errorMessage){
        if ($errorMessage === ""){
            $arrayReturned = [
                'Your inscription is successful! Welcome among us <i>'.htmlspecialchars($_POST['username']).'</i>. <br>You\'ll soon be redirected to home to confirm your inscription by logging in.',
                'formOk' => true
            ];
        }else{
            $arrayReturned = [
                $errorMessage,
                'formOk' => false
            ];
        }

        return $arrayReturned;
    }
}