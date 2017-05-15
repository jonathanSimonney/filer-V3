<?php
require_once 'model/file.php';
require_once 'model/security.php';
require_once 'model/session.php';

function open_folder($folderId){
    $folderInformations = get_file_data($folderId);
    if (user_can_access($folderInformations)){
        array_push($_SESSION['location']['array'], $folderInformations['id'], 'childs');
        $_SESSION['location']['simple'] = $folderInformations['id'];
    }
}

function close_current_folder(){
    for ($i = 0;$i !== 3;$i++){
        array_pop($_SESSION['location']['array']);
    }

    $_SESSION['location']['simple'] = array_pop($_SESSION['location']['array']);

    array_push($_SESSION['location']['array'], $_SESSION['location']['simple'], 'childs');

    if ($_SESSION['location']['simple'] === null){
        user_session_location_init();
    }
}