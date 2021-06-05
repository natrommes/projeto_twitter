<?php

require_once '../models/users.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        header("HTTP/1.1 400 Bad request");
        $message = 'Username and password are required!';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (login($username, $password)) {
            header("Location: /my_profile");
            die();
        } else {
            $message = 'Bad credentials';
        }
    }
}

if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 403:
            header('HTTP/1.1 403 Forbidden');
            $message = 'Access denied!';
            break;
        case "supOk": 
            $info = "Account created!";
            break;   
    }
}

include '../views/login.phtml';