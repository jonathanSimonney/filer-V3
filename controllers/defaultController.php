<?php
namespace controllers;

require_once 'model/db.php';
require_once 'model/user.php';
require_once 'model/security.php';
require_once 'model/session.php';

class defaultController extends BaseController
{
    public function homeAction(){
        is_logged_in();
        $_SESSION['location']['files'] = get_item_in_array($_SESSION['location']['array'],$_SESSION);
        $arrayElements = $_SESSION['location']['files'];

        //var_dump($arrayElements, $_SESSION['location'], $_SESSION['files']);


        if ($arrayElements !== null){

            $arrayElements = order_between_files_and_folder($arrayElements);
        }
        else
        {
            $arrayElements = [];
        }

        echo $this->renderView('home.html.twig', ['location' => $_SESSION['location']['simple'], 'arrayElement' => $arrayElements]);



        /*if ($arrayElements !== null){
            $numberForId = 0;

            $arrayElements = order_between_files_and_folder($arrayElements);

            foreach ($arrayElements as $key => $value){
                //var_dump(get_real_path_to_file($value));
                $numberForId++;
                if ($value['type'] === ''){
                    require 'views/inc/folder.html.twig';
                }else{
                    require 'views/inc/file.html.twig';
                }
            }
        }*/
        $_SESSION['errorMessage'] = '';
    }
}