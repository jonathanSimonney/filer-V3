<?php

namespace Router;

use Twig_Environment;

class Router
{
    private $routes;
    private $twig;
    public function __construct($routes = [], Twig_Environment $twig)
    {
        $this->routes = $routes;
        $this->twig = $twig;
    }
    public function callAction($route)
    {
        if (isset($this->routes[$route])) {
            $parts = explode(':', $this->routes[$route]);
            $controller = $parts[0] . 'Controller';
            $method = $parts[1] . 'Action';

            $controller_class = 'controllers\\' . $controller;
            $controller = new $controller_class($this->twig, $parts[2]);
            call_user_func([$controller,$method]);
        } else
        {
            die('Illegal route');
        }
    }
}