<?php

namespace controllers;
require_once 'model/file.php';
require_once 'model/user.php';
require_once 'model/security.php';
require_once 'model/form_check.php';
require_once 'model/log.php';

is_logged_in();//todo put it into the routing system.(put bool to enable check for connection of user.)

class fileController extends BaseController
{
    public function uploadAction(){
        $fileInformations = format_file_info($_FILES['file'], $_POST['name']);
        if (is_upload_possible($_FILES['file'], $fileInformations)) {
            make_upload($_FILES['file'], $fileInformations);
            writeToLog(generateAccessMessage('uploaded file '.$_POST['name'].', of id '.get_last_inserted_id()), 'access');
        }
        header('Location: ?action=home');
        exit();
    }

    public function downloadAction(){
        $fileData = get_file_data($_GET['fileId']);
        if (user_can_access($fileData)){
            download_file($fileData);
            writeToLog(generateAccessMessage('downloaded file '.get_name_with_extent($fileData['name']).', of id '.$fileData['id']), 'access');
        }
    }

    public function replaceAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $fileData = get_file_data($_POST['notForUser']);
            if (user_can_access($fileData)){
                if (is_new_file_ok($fileData)){//Did not merge these 2 if because both implement the $_SESSION['errorMessage']
                    replace_file(get_real_path_to_file($fileData), $_FILES['file']);
                    writeToLog(generateAccessMessage('replaced file '.get_name_with_extent($fileData).', of id '.$fileData['id'].' by another file'), 'access');
                }else{
                    writeToLog(generateAccessMessage('wanted to replace file '.get_name_with_extent($fileData).', of id '.$fileData['id'].' by a .'.$fileData['type']), 'access');
                }
            }
        }else{
            writeToLog(generateAccessMessage('tried to access replace page with GET request method.'), 'security');
            $_SESSION['errorMessage'] = 'Please access pages with provided links, not by writing yourself url.';
        }
        header('Location: ?action=home');
        exit();
    }

    public function renameAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $fileData = get_file_data($_POST['notForUser']);
            if (user_can_access($fileData)){
                $newFileData = format_new_file_data($fileData);
                if (is_name_ok($newFileData)){
                    rename_file($fileData, $newFileData);
                    writeToLog(generateAccessMessage('renamed file (or folder) '.get_name_with_extent($fileData).', of id '.$fileData['id'].' into '.$newFileData['name'].'.'), 'access');
                }else{
                    writeToLog(generateAccessMessage('TRIED to rename file (or folder) '.get_name_with_extent($fileData).', of id '.$fileData['id'].' into '.$newFileData['name'].'.'), 'access');
                }
            }
        }else{
            writeToLog(generateAccessMessage('tried to access rename page with GET request method.'), 'security');
            $_SESSION['errorMessage'] = 'Please access pages with provided links, not by writing yourself url.';
        }
        header('Location: ?action=home');
        exit();
    }

    public function removeAction(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $fileData = get_file_data($_POST['notForUser']);
            if (user_can_access($fileData)){
                suppress_file($fileData);
                writeToLog(generateAccessMessage('erased file or folder '.get_name_with_extent($fileData).' of id '.$fileData['id']), 'access');
            }
        }else{
            writeToLog(generateAccessMessage('tried to access remove page with GET request method.'), 'security');
            $_SESSION['errorMessage'] = 'Please access pages with provided links, not by writing yourself url.';
        }
        header('Location: ?action=home');
        exit();
    }

    public function addFolderAction(){
        $folderInformations = format_folder_info($_POST['name']);
        if (is_name_ok($folderInformations)) {
            //var_dump($folderInformations);
            add_folder($folderInformations);
            writeToLog(generateAccessMessage('created folder '.$folderInformations['name'].', of id '.get_last_inserted_id()), 'access');
        }else{
            writeToLog(generateAccessMessage('tried to add a folder of name'.$folderInformations['name']), 'access');
        }

        header('Location: ?action=home');
        exit();
    }

    public function moveAction(){
        $movedElementData = get_file_data($_GET['idMovedElement']);
        $toParent = false;
        if ($_GET['idDestination'] === 'precedent'){
            $toParent = true;

            $currentLocation = $_SESSION['location'];

            close_current_folder();
            $destinationId = $_SESSION['location']['simple'];

            close_current_folder();

            $_SESSION['location']['files']= get_item_in_array($_SESSION['location']['array'],$_SESSION);
            $destinationFolderData = get_file_data($destinationId);
            $_SESSION['location'] = $currentLocation;
        }else{
            $destinationFolderData = get_file_data($_GET['idDestination']);
        }
        if (user_can_access($movedElementData) && user_can_access($destinationFolderData, true)){
            $newPath = generate_new_path($movedElementData, $destinationFolderData);

            db_update('files', $movedElementData['id'],['path' => $newPath]);
            move_in_session($movedElementData, $destinationFolderData, $newPath, $toParent);
            move_on_server($movedElementData, $destinationFolderData, $toParent);
            writeToLog(generateAccessMessage('moved file or folder '.get_name_with_extent($movedElementData).' of id '.$movedElementData['id'].' into folder '.$destinationFolderData['name'].' of id '.$destinationFolderData['id']), 'access');
        }


        header('Location: ?action=home');
        exit();
        //TODO check if destination does not have a file of same name.
    }

    public function showAction(){
        $fileData = get_file_data($_GET['id']);
        http_response_code(400);

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (user_can_access($fileData)){
                if ($fileData['type'] !== ''){
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
                    writeToLog(generateAccessMessage('tried to access his folder '.$fileData['name'].' of id '.$fileData['id']), 'security');
                }
            }
        }else{
            if (user_can_access($fileData)){
                if ($fileData['type'] !== ''){
                    $path = get_real_path_to_file($fileData);
                    http_response_code(200);
                    setCorrectHeader($fileData['type']);
                    readfile($path);
                    writeToLog(generateAccessMessage('saw his file '.get_name_with_extent($fileData).' of id '.$fileData['id']), 'access');
                }else{
                    writeToLog(generateAccessMessage('tried to access his folder '.$fileData['name'].' of id '.$fileData['id']), 'security');
                }
            }else{
                writeToLog(generateAccessMessage('tried to access file '.get_name_with_extent($fileData).' of id '.$fileData['id'].' belonging to user number '.$fileData['user_id']), 'security');
            }
        }
    }

    public function writeAction(){
        $fileData = get_file_data($_GET['id']);
        http_response_code(400);
        if (user_can_access($fileData)){
            //var_dump($_GET['newContent']);
            file_put_contents(get_real_path_to_file($fileData), $_GET['newContent']);
            http_response_code(200);
            writeToLog(generateAccessMessage('wrote into his file '.get_name_with_extent($fileData).' of id '.$fileData['id']), 'access');
            //echo file_get_contents(get_real_path_to_file($fileData));
        }
    }
}