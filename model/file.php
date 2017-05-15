<?php

require_once 'model/db.php';
require_once 'model/session.php';
require_once 'model/nav.php';

//var_dump($_SESSION['files'], $_SESSION['location']);

function get_file_data($fileId){
    if ($fileId !== 'root'){
        if (array_key_exists($fileId, $_SESSION['location']['files'])){
            return $_SESSION['location']['files'][$fileId];
        }else{
            //var_dump($fileId, $_SESSION['location']['files']);
            return get_what_how($fileId, 'id', 'files')[0];
        }
    }else{
        return ['name' => 'root', 'id' => 'root', 'path' => 'uploads/'.$_SESSION['currentUser']['data']['id']];
    }
}

function suppress_recursively($fileData){
    $currentLocation = $_SESSION['location'];
    open_folder($fileData['id']);
    $_SESSION['location']['files'] = get_item_in_array($_SESSION['location']['array'],$_SESSION);

    if ($_SESSION['location']['files'] !== null){
        foreach ($_SESSION['location']['files'] as $key => $value){
            if ($value['type'] === ''){
                suppress_recursively($value);
            }else{
                unlink(get_real_path_to_file($value));
                db_suppress('files',$value['id']);
            }
        }
    }

    $_SESSION['location'] = $currentLocation;
    rmdir(get_real_path_to_file($fileData));
    db_suppress('files',$fileData['id']);
}

function suppress_file($fileData){
    if ($fileData['type'] !== ''){
        unlink(get_real_path_to_file($fileData));
        db_suppress('files',$fileData['id']);
    }else{
        suppress_recursively($fileData);
    }

    unset_item_in_array(array_merge($_SESSION['location']['array'],[$fileData['id']]),$_SESSION);
}

function format_new_file_data($oldFileData){
    $newFileData['name'] = format_file_name($_POST['name'], $oldFileData['type']);
    if ($oldFileData['type'] === ''){
        $newFileData['path'] = preg_replace('/'.preg_quote($oldFileData['name'], NULL).'(?!.)/', $newFileData['name'], $oldFileData['path']);
    }else{
        //following regexp is supposed to select the oldFileName only if it is followed by its type with nothing behind.
        $newFileData['path'] = preg_replace('/'.preg_quote($oldFileData['name'], NULL).'(?=\.'.$oldFileData['type'].'(?!=.))/', $newFileData['name'], $oldFileData['path']);
    }


    return $newFileData;
}

function rename_file($oldFileData, $newFileData){
    $newFileData = array_merge($oldFileData, $newFileData);

    rename(get_real_path_to_file($oldFileData), get_real_path_to_file($newFileData));
    db_update('files', $oldFileData['id'], $newFileData);
    set_item_in_array(array_merge($_SESSION['location']['array'],[$oldFileData['id']]),$_SESSION,$newFileData);
}

function is_name_ok($fileData){//Todo check if name correspond to current name.
    $_SESSION['errorMessage'] = '';

    if ($fileData['name'] === '') {
        $_SESSION['errorMessage'] = 'You must put a name on your file.';
    }elseif($_SESSION['location']['files'] !== null){
        if (array_key_exists($fileData['path'], make_inferior_key_index($_SESSION['location']['files'], 'path'))){
            $_SESSION['errorMessage'] = 'The name '.$fileData['name'].' is already used for one of your files. Please type another name or use the replace button.';
        }
    }

    if ($_SESSION['errorMessage'] === ''){
        return true;
    }else{
        return false;
    }
}

function is_new_file_ok($oldFileData){
    if(empty($_FILES['file']['name'])){
        $_SESSION['errorMessage'] = 'You must choose a file to upload.';
        return false;
    }elseif ($oldFileData['type'] !== get_file_type($_FILES['file'])) {
        $_SESSION['errorMessage'] = 'The type of the file you wish to replace is not the same as the one you wish to upload instead';
        return false;
    }
    return true;
}

function replace_file($pathOldFile, $file){
    if (!move_uploaded_file($file["tmp_name"], $pathOldFile)){
        $_SESSION["errorMessage"] = "your file wasn't uploaded. Please try seeing if your username is a valid one.";
    }
}

function download_file($fileData){
    // Specify file path.
    $download_file =  get_real_path_to_file($fileData);
    // Getting file extension.
    $extension = $fileData['type'];
    // For Gecko browsers
    header('Content-Transfer-Encoding: binary');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(str_replace($fileData['name'].'.'.$extension, '', $download_file))) . ' GMT');
    // Supports for download resume
    header('Accept-Ranges: bytes');
    // Calculate File size
    header('Content-Length: ' . filesize($download_file));
    header('Content-Encoding: none');
    // Change the mime type if the file is not PDF
    header('Content-Type: application/' . $extension);
    // Make the browser display the Save As dialog
    header('Content-Disposition: attachment; filename=' . $fileData['name'].'.'.$extension);
    readfile($download_file);
    exit;
}

function format_file_name($nameFile, $type){
    $nameFile = preg_replace('/'.$type.'(?!.)/', '', $nameFile);
    $nameFile = urlencode($nameFile);
    return $nameFile;
}

function get_file_type($file){
    $type = '';
    if (!empty($file['name'])){
        preg_match('/\.[0-9a-z]+$/', $file["name"], $cor);
        $type = $cor[0];
    }

    $type = str_replace(".", "", $type);
    return $type;
}

function format_file_info($file, $nameFile){
    $type = get_file_type($file);


    $nameFile = format_file_name($nameFile, '\.'.$type);
    if ($_SESSION['location']['simple'] === 'root'){
        $pathFile = 'uploads/'.$_SESSION['currentUser']['data']['id'].'/'.$nameFile.'.'.$type;
    }else{
        $pathFile = (string)$_SESSION['location']['simple'].'/'.$nameFile.'.'.$type;
    }


    return [
        'type' => $type,
        'name' => $nameFile,
        'path' => $pathFile
    ];
}

function format_folder_info($nameFile){
    $nameFile = urlencode($nameFile);

    if ($_SESSION['location']['simple'] === 'root'){
        $pathFile = 'uploads/'.$_SESSION['currentUser']['data']['id'].'/'.$nameFile;
    }else{
        $pathFile = (string)$_SESSION['location']['simple'].'/'.$nameFile;
    }

    return [
        'type' => '',
        'name' => $nameFile,
        'path' => $pathFile
    ];
}

function is_upload_possible($file, $fileInformations){
    $_SESSION['errorMessage'] = '';

    if ($fileInformations['name'] === '') {
        $_SESSION['errorMessage'] = 'You must put a name on your file.';
    }elseif (array_key_exists($fileInformations['path'], make_inferior_key_index($_SESSION['location']['files'], 'path'))){
        $_SESSION['errorMessage'] = 'The name '.$fileInformations['name'].' is already used for one of your files. Please type another name or use the replace button.';
    }elseif($file['name'] === ''){
        $_SESSION['errorMessage'] = 'You must choose a file to upload.';
    }

    if ($_SESSION['errorMessage'] === ''){
        return true;
    }else{
        return false;
    }
}

function upload_file_in_folder($file, $path){
    if (!move_uploaded_file($file['tmp_name'], $path)){
        $_SESSION['errorMessage'] = "your file wasn't uploaded. Please check it is not too big (max upload size is of 8MB).";
        return false;
    }else{
        return true;
    }
}

function upload_file_in_db($fileInformations){
    $fileInformations['user_id'] = $_SESSION['currentUser']['data']['id'];
    db_insert('files', $fileInformations, true);
}

function get_real_path_to_file($fileInformations){
    if ($fileInformations === 'root'){
        $path = 'uploads/'.$_SESSION['currentUser']['data']['id'];
    }elseif ($_SESSION['location']['simple'] === 'root'){
        $path = $fileInformations['path'];
    }else{
        $path = '';
        $cursor = [];
        $first = true;
        foreach ($_SESSION['location']['array'] as $key => $value){
            $cursor[] = $value;
            if ($key%2 === 1){
                $addedPath = get_item_in_array($cursor, $_SESSION)['path'];
                if ($first){
                    $first = false;
                }else{
                    $addedPath = preg_replace('/\d+(?=\/)/','',$addedPath);
                }

                $path .= $addedPath;
            }
        }
        $path .= '/'.get_name_with_extent($fileInformations);
    }

    return $path;
}

function make_upload($file, $fileInformations){
    $path = get_real_path_to_file($fileInformations);

    if (upload_file_in_folder($file, $path)){
        upload_file_in_db($fileInformations);
        upload_file_in_session($fileInformations);
    }
}

function add_folder($folderInformations){
    mkdir(get_real_path_to_file($folderInformations));
    upload_file_in_db($folderInformations);
    upload_file_in_session($folderInformations);
}

function generate_new_path($movedElementData, $destinationData){
    if ($destinationData['id'] === 'root'){
        $beginning = 'uploads/'.$_SESSION['currentUser']['data']['id'];
    }else{
        $beginning = $destinationData['id'];
    }

    return $beginning.'/'.get_name_with_extent($movedElementData);
}

function get_name_with_extent($fileOrFolderData){
    $name = $fileOrFolderData['name'];
    if ($fileOrFolderData['type'] !== ''){
        $name .= '.'.$fileOrFolderData['type'];
    }

    return $name;
}

function move_on_server($movedElementData, $destinationFolderData, $toParent = false){
    $currentLocation = $_SESSION['location'];

    $currentPath = get_real_path_to_file($movedElementData);
    if ($toParent){
        close_current_folder();
        close_current_folder();
    }

    rename($currentPath, get_real_path_to_file($destinationFolderData).'/'.get_name_with_extent($movedElementData));

    $_SESSION['location'] = $currentLocation;
}

function order_between_files_and_folder($arrayToOrder){
    $arrayFiles = [];
    $arrayFolders = [];

    foreach ($arrayToOrder as $key => $value){
        if ($value['type'] === ''){
            $arrayFolders[] = $value;
        }else{
            $arrayFiles[] = $value;
        }
        $arrayToOrder = array_merge($arrayFolders, $arrayFiles);
    }

    return $arrayToOrder;
}

function setCorrectHeader($type){
    switch ($type){
        case 'txt' :
            header('Content-Type: text/'.$type);
            break;
        case 'jpg' :
        case 'jpeg':
        case 'gif' :
        case 'ani' :
        case 'bmp' :
        case 'cal' :
        case 'fax' :
        case 'img' :
        case 'jbg' :
        case 'jpe' :
        case 'mac' :
        case 'pbm' :
        case 'pcd' :
        case 'pcx' :
        case 'pct' :
        case 'pgm' :
        case 'png' :
        case 'ppm' :
        case 'psd' :
        case 'ras' :
        case 'tga' :
        case 'tiff':
        case 'wmf' :
            header('Content-Type: image/'.$type);
            break;
        case 'mp3':
            header('Content-Type: audio/'.$type);
            break;
        case 'avi':
        case 'asf':
        case 'mov':
        case 'qt':
        case 'avchd':
        case 'slv':
        case 'fwf':
        case 'mpg':
        case 'mp4':
            header('Content-Type: video/'.$type);
            break;

        default :
            return false;
            break;
    }

    return true;
}