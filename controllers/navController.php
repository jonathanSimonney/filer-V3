<?php
namespace controllers;


use Model\NavManager;

require_once 'model/nav.php';

class navController extends BaseController
{
    protected $navManager;

    public function __construct(\Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->navManager = NavManager::getInstance();
    }

    public function openAction(){
        $this->navManager->openFolder($_GET['fileId']);
        header('Location: ?action=home');
        exit();
    }

    public function toParentAction(){
        $this->navManager->closeCurrentFolder();

        header('Location: ?action=home');
        exit();
    }
}