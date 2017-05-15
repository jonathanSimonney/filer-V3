<?php

require_once 'model/log.php';

function user_can_access($fileData, $canBeRoot = false){
    if ($fileData['name'] === 'root' && $canBeRoot){
        return true;
    }elseif($fileData['user_id'] === $_SESSION['currentUser']['data']['id']){
        return true;
    }else{
        writeToLog(generateAccessMessage('tried to access folder '.$fileData['name'].' of id '.$fileData['id'].' belonging to user number'.$fileData['user_id']), 'security');
        $_SESSION['errorMessage'] = 'You tried to access a file which wasn\'t one of your files.';
        return false;
    }
}

function is_logged_in(){
    if (!$_SESSION['currentUser']['loggedIn']) {
        writeToLog(generateAccessMessage('tried to make '.$_GET['action']), 'security');
        $_SESSION['errorMessage'] = 'Sorry, but you tried to access a page without authorisation. Please log in.';
        header('Location: ?action=login');
        exit(0);
    }
}