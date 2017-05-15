<?php
require_once('model/db.php');

function requiredField($name){
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
    }else{
        return true;
    }
}

function check_required_field($arrayRequiredField){
    foreach ($arrayRequiredField as $item) {
        requiredField($item);
    }
}

function isNotAlreadyInDb($needle, $column, $table){
    if (get_what_how($needle, $column, $table)){
        $_SESSION['errorMessage'] .= "Sorry, but the ".$column." <i>".htmlspecialchars($needle)."</i> is already taken, please choose another one.<br>";
    }
}

function check_uniq_field($objectUniqField){
    foreach ($objectUniqField as $item => $table) {
        isNotAlreadyInDb($_POST[$item], $item, $table);
    }
}

function check_email($potentialEmail){
    $re = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
    if (preg_match($re, $potentialEmail) !== 1) {
        $_SESSION['errorMessage'] .= 'Your email address is invalid.<br>';
    }
}

function check_password($password, $confirmationPassword){
    if ($password !== $confirmationPassword) {
        $_SESSION['errorMessage'] .= 'You must write the same thing in the fields password and confirmation of password<br>';
    }//BONUS : add a security level of password

    if (strlen($password) <= 8) {
        $_SESSION['errorMessage'] .= 'your password must do at least 8 characters';
    }
}

function get_array_returned($errorMessage){
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