<?php
use Router\Router;
use Symfony\Component\Yaml\Yaml;

session_start();
error_reporting(0);

require __DIR__ . '/vendor/autoload.php';

if (!array_key_exists('errorMessage', $_SESSION)){
    $_SESSION['errorMessage'] = '';
}

$publicConfig = Yaml::parse(file_get_contents('config/config.yml'));
$privateConfig = Yaml::parse(file_get_contents('config/private.yml'));

if (empty($_GET['action'])){
    $_GET['action'] = $publicConfig['defaultAction'];
}

$action = $_GET['action'];

$loader = new Twig_Loader_Filesystem('views/');
$twig = new Twig_Environment($loader, array(//todo activate cache, deactivate debug BEFORE FINAL COMMIT! IMPORTANT!!!!
    'cache' => 'cache/twig/',
    //'cache' => false,
    //'debug' => true
));
//$twig->addExtension(new Twig_Extension_Debug());//todo activate cache, deactivate debug BEFORE FINAL COMMIT! IMPORTANT!!!!

$router = new Router($publicConfig['routes'], $twig);
$router->callAction($action);