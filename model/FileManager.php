<?php

namespace Model;

class FileManager extends BaseManager
{
    protected $sessionManager;
    protected $dbManager;
    protected $navManager;

    public function setup()
    {
        $this->sessionManager = SessionManager::getInstance();
        $this->navManager = NavManager::getInstance();
        $this->dbManager = DbManager::getInstance();
    }

    protected function moveOnServer($movedElementData, $destinationFolderData, $toParent = false){
        $currentLocation = $_SESSION['location'];

        $currentPath = $this->getRealPathToFile($movedElementData);
        if ($toParent){
            $this->navManager->closeCurrentFolder();
            $this->navManager->closeCurrentFolder();
        }

        rename($currentPath, $this->getRealPathToFile($destinationFolderData).'/'.$this->getNameWithExtent($movedElementData));

        $_SESSION['location'] = $currentLocation;
    }

    protected function suppressRecursively($fileData){
        $currentLocation = $_SESSION['location'];
        $this->navManager->openFolder($fileData['id']);
        $_SESSION['location']['files'] = $this->sessionManager->getItemInArray($_SESSION['location']['array'],$_SESSION);

        if ($_SESSION['location']['files'] !== null){
            foreach ($_SESSION['location']['files'] as $key => $value){
                if ($value['type'] === ''){
                    $this->suppressRecursively($value);
                }else{
                    unlink($this->getRealPathToFile($value));
                    $this->dbManager->removeFromDb('files',$value['id']);
                }
            }
        }

        $_SESSION['location'] = $currentLocation;
        rmdir($this->getRealPathToFile($fileData));
        $this->dbManager->removeFromDb('files',$fileData['id']);
    }

    protected function formatFileName($nameFile, $type){
        $nameFile = preg_replace('/'.$type.'(?!.)/', '', $nameFile);
        $nameFile = urlencode($nameFile);
        return $nameFile;
    }

    protected function getFileType($file){
        $type = '';
        if (!empty($file['name'])){
            preg_match('/\.[0-9a-z]+$/', $file["name"], $cor);
            $type = $cor[0];
        }

        $type = str_replace(".", "", $type);
        return $type;
    }

    protected function uploadFileInFolder($file, $path){
        if (!move_uploaded_file($file['tmp_name'], $path)){
            $_SESSION['errorMessage'] = "your file wasn't uploaded. Please check it is not too big (max upload size is of 8MB).";
            return false;
        }

        return true;
    }

    protected function uploadFileInDb($fileInformations){
        $fileInformations['user_id'] = $_SESSION['currentUser']['data']['id'];
        $this->dbManager->dbInsert('files', $fileInformations, true);
    }

    public function getFileData($fileId){
        if ($fileId !== 'root'){
            if (array_key_exists($fileId, $_SESSION['location']['files'])){
                return $_SESSION['location']['files'][$fileId];
            }
            return $this->dbManager->getWhatHow($fileId, 'id', 'files')[0];
        }
        return ['name' => 'root', 'id' => 'root', 'path' => 'uploads/'.$_SESSION['currentUser']['data']['id']];
    }

    public function suppressFile($fileData){
        if ($fileData['type'] !== ''){
            unlink($this->getRealPathToFile($fileData));
            $this->dbManager->removeFromDb('files',$fileData['id']);
        }else{
            $this->suppressRecursively($fileData);
        }

        $this->sessionManager->unsetItemInArray(array_merge($_SESSION['location']['array'],[$fileData['id']]),$_SESSION);
    }

    public function formatNewFileData($oldFileData){
        $newFileData['name'] = $this->formatFileName($_POST['name'], $oldFileData['type']);
        if ($oldFileData['type'] === ''){
            $newFileData['path'] = preg_replace('/'.preg_quote($oldFileData['name'], NULL).'(?!.)/', $newFileData['name'], $oldFileData['path']);
        }else{
            //following regexp is supposed to select the oldFileName only if it is followed by its type with nothing behind.
            $newFileData['path'] = preg_replace('/'.preg_quote($oldFileData['name'], NULL).'(?=\.'.$oldFileData['type'].'(?!=.))/', $newFileData['name'], $oldFileData['path']);
        }


        return $newFileData;
    }

    public function renameFile($oldFileData, $newFileData){
        $newFileData = array_merge($oldFileData, $newFileData);

        rename($this->getRealPathToFile($oldFileData), $this->getRealPathToFile($newFileData));
        $this->dbManager->dbUpdate('files', $oldFileData['id'], $newFileData);
        $this->sessionManager->setItemInArray(array_merge($_SESSION['location']['array'],[$oldFileData['id']]),$_SESSION,$newFileData);
    }

    public function isNameOk($fileData){//Todo check if name correspond to current name.
        $_SESSION['errorMessage'] = '';

        if ($fileData['name'] === '') {
            $_SESSION['errorMessage'] = 'You must put a name on your file.';
        }elseif($_SESSION['location']['files'] !== null){
            if (array_key_exists($fileData['path'], $this->makeInferiorKeyIndex($_SESSION['location']['files'], 'path'))){
                $_SESSION['errorMessage'] = 'The name '.$fileData['name'].' is already used for one of your files. Please type another name or use the replace button.';
            }
        }

        return $_SESSION['errorMessage'] === '';
    }

    public function isNewFileOk($oldFileData){
        if(empty($_FILES['file']['name'])){
            $_SESSION['errorMessage'] = 'You must choose a file to upload.';
            return false;
        }

        if ($oldFileData['type'] !== $this->getFileType($_FILES['file'])) {
            $_SESSION['errorMessage'] = 'The type of the file you wish to replace is not the same as the one you wish to upload instead';
            return false;
        }
        return true;
    }

    public function replaceFile($pathOldFile, $file){
        if (!move_uploaded_file($file["tmp_name"], $pathOldFile)){
            $_SESSION["errorMessage"] = "your file wasn't uploaded. Please try seeing if your username is a valid one.";
        }
    }

    public function downloadFile($fileData){
        // Specify file path.
        $downloadFile =  $this->getRealPathToFile($fileData);
        // Getting file extension.
        $extension = $fileData['type'];
        // For Gecko browsers
        header('Content-Transfer-Encoding: binary');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(str_replace($fileData['name'].'.'.$extension, '', $downloadFile))) . ' GMT');
        // Supports for download resume
        header('Accept-Ranges: bytes');
        // Calculate File size
        header('Content-Length: ' . filesize($downloadFile));
        header('Content-Encoding: none');
        // Change the mime type if the file is not PDF
        header('Content-Type: application/' . $extension);
        // Make the browser display the Save As dialog
        header('Content-Disposition: attachment; filename=' . $fileData['name'].'.'.$extension);
        readfile($downloadFile);
        exit;
    }

    public function formatFileInfo($file, $nameFile){
        $type = $this->getFileType($file);


        $nameFile = $this->formatFileName($nameFile, '\.'.$type);
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

    public function formatFolderInfo($nameFile){
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

    public function isUploadPossible($file, $fileInformations){
        $_SESSION['errorMessage'] = '';

        if ($fileInformations['name'] === '') {
            $_SESSION['errorMessage'] = 'You must put a name on your file.';
        }elseif (array_key_exists($fileInformations['path'], $this->makeInferiorKeyIndex($_SESSION['location']['files'], 'path'))){
            $_SESSION['errorMessage'] = 'The name '.$fileInformations['name'].' is already used for one of your files. Please type another name or use the replace button.';
        }elseif($file['name'] === ''){
            $_SESSION['errorMessage'] = 'You must choose a file to upload.';
        }

        return $_SESSION['errorMessage'] === '';
    }

    public function getRealPathToFile($fileInformations){
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
                    $addedPath = $this->sessionManager->getItemInArray($cursor, $_SESSION)['path'];
                    if ($first){
                        $first = false;
                    }else{
                        $addedPath = preg_replace('/\d+(?=\/)/','',$addedPath);
                    }

                    $path .= $addedPath;
                }
            }
            $path .= '/'.$this->getNameWithExtent($fileInformations);
        }

        return $path;
    }

    public function makeUpload($file, $fileInformations){
        $path = $this->getRealPathToFile($fileInformations);

        if ($this->uploadFileInFolder($file, $path)){
            $this->uploadFileInDb($fileInformations);
            $this->sessionManager->uploadFileInSession($fileInformations);
        }
    }

    public function addFolder($folderInformations){
        mkdir($this->getRealPathToFile($folderInformations));
        $this->uploadFileInDb($folderInformations);
        $this->sessionManager->uploadFileInSession($folderInformations);
    }

    public function generateNewPath($movedElementData, $destinationData){
        if ($destinationData['id'] === 'root'){
            $beginning = 'uploads/'.$_SESSION['currentUser']['data']['id'];
        }else{
            $beginning = $destinationData['id'];
        }

        return $beginning.'/'.$this->getNameWithExtent($movedElementData);
    }

    public function getNameWithExtent($fileOrFolderData){
        $name = $fileOrFolderData['name'];
        if ($fileOrFolderData['type'] !== ''){
            $name .= '.'.$fileOrFolderData['type'];
        }

        return $name;
    }

    public function orderBetweenFilesAndFolder($arrayToOrder){
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

    public function setCorrectHeader($type){
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

    public function moveFile(bool $toParent, $movedElementData, $destinationFolderData)
    {
        $newPath = $this->generateNewPath($movedElementData, $destinationFolderData);

        $this->dbManager->dbUpdate('files', $movedElementData['id'],['path' => $newPath]);
        $this->sessionManager->moveInSession($movedElementData, $destinationFolderData, $newPath, $toParent);
        $this->moveOnServer($movedElementData, $destinationFolderData, $toParent);
    }
}

//var_dump($_SESSION['files'], $_SESSION['location']);

