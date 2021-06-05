<?php

require_once '../models/users.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];

    if (signUp($username, $password, $name)) {
        header("Location: /login?status=supOk");
        die();
    }

    $message = 'Error processing sign up!';
}

include '../views/signup.phtml';