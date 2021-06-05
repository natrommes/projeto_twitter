<?php

$routes = [
    '/login'            => 'login.php',
    '/logout'           => 'logout.php',
    '/register'         => 'signup.php',
    '/about'            => 'about.php',
    '/my_profile'       => 'pag_my_profile.php',
    '/profiles'         => 'pag_profiles.php',
    '/unfollow'         => 'pag_profiles.php',
    '/public_malta'     => 'pag_public_malta.php',
];

session_start();

$route = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/register';

if (isset($_SESSION['user']) && ($route == '/login')) {  
    $message = 'Primeiro você deve deslogar, para depois fazer o login novamente.';
    $route = '/my_profile'; 
};

if (isset($routes[$route])) {
    $controller = "../controllers/" . $routes[$route];

    if (file_exists($controller)) {
        require_once $controller;
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        die("Controller file not found");
    }
} else {
    header("HTTP/1.1 404 Not Found");
    die("Route $route not found");
}
