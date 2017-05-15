<?php
use Router\Router;

session_start();

require __DIR__ . '/vendor/autoload.php';

if (!array_key_exists('errorMessage', $_SESSION)){
    $_SESSION['errorMessage'] = '';
}

require('config/config.php');

if (empty($_GET['action'])){
    $_GET['action'] = 'home';
}

$action = $_GET['action'];

$loader = new Twig_Loader_Filesystem('views/');
$twig = new Twig_Environment($loader, array(//todo activate cache, deactivate debug BEFORE FINAL COMMIT! IMPORTANT!!!!
    // 'cache' => 'cache/twig/',
    'cache' => false,
    'debug' => true
));
$twig->addExtension(new Twig_Extension_Debug());//todo activate cache, deactivate debug BEFORE FINAL COMMIT! IMPORTANT!!!!

$router = new Router($routes, $twig);
$router->callAction($action);