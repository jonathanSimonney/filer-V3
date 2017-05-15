<?php
namespace controllers;


require_once 'model/nav.php';

class navController extends BaseController
{
    public function openAction(){
        open_folder($_GET['fileId']);
        header('Location: ?action=home');
        exit();
    }

    public function toParentAction(){
        close_current_folder();

        header('Location: ?action=home');
        exit();
    }
}