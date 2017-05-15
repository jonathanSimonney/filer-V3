<?php

function generateAccessMessage($action){

    if (!empty($_SESSION['currentUser']['data']['username'])){
        $begin = 'User '.$_SESSION['currentUser']['data']['username'];
    }else{
        $begin = 'Unknown user';
    }
    return $begin.' '.$action.' at '.date('r');
}

function writeToLog($newMessage, $file){
    if ($file === 'access'){
        $file = fopen('logs/access.log', 'ab');
    }else{
        $file = fopen('logs/security.log', 'ab');
    }
    fwrite($file, $newMessage."\n");

    fclose($file);
}