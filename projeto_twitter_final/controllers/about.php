<?php

require_once '../models/users.php';


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

include '../views/about.phtml';