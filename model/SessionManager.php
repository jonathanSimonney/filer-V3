<?php
namespace Model;

class SessionManager extends BaseManager
{
    protected $navManager;
    protected $dbManager;

    public function setup()
    {
        $this->navManager = NavManager::getInstance();
        $this->dbManager = DbManager::getInstance();
    }

    protected function updatePath($idParent, $suppressedArray, $key, $path){
        $path = array_merge([$idParent, 'childs'],$path);
        if (array_key_exists($idParent, $suppressedArray)){
            $newData = $this->updatePath($suppressedArray[$idParent], $suppressedArray, $key, $path);
            $suppressedArray = $newData['suppressedArray'];
            $path = $newData['path'];
        }
        $suppressedArray[$key] = $idParent;
        //var_dump($suppressedArray, $path);

        return [
            'suppressedArray' => $suppressedArray,
            'path'            => $path
        ];
    }

    protected function getParentLocation(){
        $currentLocation = $_SESSION['location'];
        $this->navManager->closeCurrentFolder();
        $parentLocation = $_SESSION['location'];
        $_SESSION['location'] = $currentLocation;

        return $parentLocation;
    }

    public function findCorrespondingElements($superArray, $needleKey, $needleValue){
        $array_analysed = $this->makeInferiorKeyIndex($superArray, $needleKey);
        foreach ($array_analysed as $key => $array){
            if ((string)$key === (string)$needleValue){
                $ret[] = $array;
            }
        }

        if (!isset($ret)){
            $ret = false;
        }

        return $ret;
    }

    public function uploadFileInSession($fileInformations){
        $fileInformations['id'] = $this->dbManager->getLastInsertedId();
        $fileInformations['user_id'] = $_SESSION['currentUser']['data']['id'];
        $this->setItemInArray(array_merge($_SESSION['location']['array'], [$fileInformations['id']]), $_SESSION, $fileInformations);
        //var_dump($_SESSION['files'][110]['childs'], $_SESSION['location']['array'], $fileInformations);
    }

    public function setItemInArray($path, &$array, $value){
        $key = array_shift($path);
        if (empty($path)) {
            $array[$key] = $value;
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }
            $this->setItemInArray( $path, $array[$key], $value);
        }
    }

    public function getItemInArray($path, &$array){
        $key = array_shift($path);
        if (empty($path)) {
            if (array_key_exists($key, $array)){
                return $array[$key];
            }

            return null;
        }

        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = array();
        }
        return $this->getItemInArray( $path, $array[$key]);
    }

    public function unsetItemInArray($path, &$array){
        $key = array_shift($path);
        if (empty($path)) {
            unset($array[$key]);
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }
            $this->unsetItemInArray( $path, $array[$key]);
        }
    }

    public function formatSessionFileAsTree(){
        $arrayAsTree = [];
        $suppressedArray = [];
        foreach ($_SESSION['files'] as $key => $value){
            $path = [$key];
            if ($value['path'][0] !== 'u' && preg_match('/\d+(?=\/)/', $value['path'], $cor) === 1){//we're not at the root folder, AND there is a number stocked in $cor
                $newData = $this->updatePath((int)$cor[0], $suppressedArray, $key, $path);
                $suppressedArray = $newData['suppressedArray'];
                $path = $newData['path'];
            }

            if ($this->getItemInArray($path, $arrayAsTree) !== null){
                $value = array_merge($this->getItemInArray($path, $arrayAsTree), $value);
                $this->unsetItemInArray($path, $arrayAsTree);
            }

            $precedentValue = $this->getItemInArray([$value['id']], $arrayAsTree);

            if ($precedentValue !== null){
                $value = array_merge($this->getItemInArray([$value['id']], $arrayAsTree), $value);
                $this->unsetItemInArray([$value['id']], $arrayAsTree);
            }

            $this->setItemInArray($path, $arrayAsTree, $value);
            //var_dump($arrayAsTree, $value, $path, getItemInArray($path, $arrayAsTree));
        }

        $_SESSION['files'] = $arrayAsTree;
    }

    public function userSessionLocationInit(){
        $_SESSION['location']['array'] = ['files'];
        $_SESSION['location']['simple'] = 'root';
    }

    public function moveInSession($movedElementData, $destinationFolderData, $newPath, $toPrecedent = false){
        $copiedElement = $this->getItemInArray($_SESSION['location']['array'], $_SESSION)[$movedElementData['id']];
        $copiedElement['path'] = $newPath;
        $this->unsetItemInArray(array_merge($_SESSION['location']['array'], [$movedElementData['id']]), $_SESSION);

        if ($toPrecedent){
            $parentLocation = $this->getParentLocation();
            $this->setItemInArray(array_merge($parentLocation['array'], [$movedElementData['id']]),$_SESSION, $copiedElement);
        }else{
            $this->setItemInArray(array_merge($_SESSION['location']['array'], [$destinationFolderData['id'], 'childs', $movedElementData['id']]), $_SESSION, $copiedElement);
        }
    }
}