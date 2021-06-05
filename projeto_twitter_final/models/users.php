<?php

require_once '../core/database.php';


function getUserByUsername($username) {
    $connection = getConnection();
    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $stmt = $connection->query($sql);

    $user = $stmt->fetch();
    return $user ? $user : null;
}

function login($username, $password) {
    $user = getUserByUsername($username);
    
    if (!is_null($user) && password_verify($password, $user['password'])) {
        session_start(); 
        $_SESSION['user'] = $user['username'];

        logUserAttempt($username, 'logged in');
        return true;
    }

    logUserAttempt($username, 'fail logged in');
    return false;
}

function signUp($username, $password, $name) {
    $connection = getConnection();
    $password = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, name, password) VALUES ('$username', '$name', '$password')";
    $stmt = $connection->query($sql);
  
    return $stmt;
}

function logout() {
    session_start();
    $user = $_SESSION['user'];
    unset($_SESSION['user']);

    logUserAttempt($user['username'], 'logged out');
}

function verifyAccess() {
   if (!isset($_SESSION['user'])) {  
        header("HTTP/1.1 403 Forbidden");
        header("Location: /login?status=403"); 
        die();
    }
 return $_SESSION['user'];
}

function logUserAttempt($username, $message) {
    $log = sprintf(
        "[%s] user %s %s from %s\n",
        date('Y-m-d H:i:s'),
        $username,
        $message,
        $_SERVER['REMOTE_ADDR']
    );
    file_put_contents('../logs/access.log', $log, FILE_APPEND);
}

function writeAboutByUsername ($tellAbout, $username) { 
        $sql = "UPDATE users SET about = '$tellAbout' WHERE username = '$username'"; 
        $connection = getConnection();
        $stmt = $connection->query($sql);
        $stmt->rowCount() > 0;
}

function writeImageByUsername ($arquivo, $username) {
         $sql = "UPDATE users SET image = '$arquivo' WHERE username = '$username'";
         $connection = getConnection();
         $stmt = $connection->query($sql);
         return $stmt->rowCount() > 0;
}

function writeMessageByUsername ($writePost, $datePost, $user_id) {
      $sql = "INSERT INTO post(message, date, users_id) VALUES('$writePost', '$datePost', $user_id)";
      $connection = getConnection();
      $stmt = $connection->query($sql);
      return $stmt->rowCount() > 0;
}

function getPostbyUsername ($user_id){
    $sql = "SELECT p.*, u.name, u.username  FROM post p, users u WHERE (p.users_id IN (SELECT id_seguidores FROM follow WHERE users_id = $user_id) OR p.users_id = $user_id) AND p.users_id = u.id ORDER BY p.id DESC LIMIT 5";
    $connection = getConnection();
    $stmt = $connection->query($sql);
    
    $allPosts = $stmt->fetchAll();
    return $allPosts ? $allPosts : null;
}

function getAllPostbygetAllUser ($filter = null, $username) {
    $sql = "SELECT * FROM users, post WHERE name != ''"; 

    if (!is_null($filter)) {
        $sql .= " AND name LIKE '%$filter%'"; 
    }
    $connection = getConnection();
    $stmt = $connection->query($sql);
    $posts = $stmt->fetchAll();
    for ($i=0; $i<count($posts);$i++) {
        $sql = "SELECT followers, 
        IF (
        message IS NULL,
        'This user has no posts',
         message) AS message,
         followed
        FROM (
        SELECT COUNT(*) AS followers FROM post WHERE users_id = ".$posts[$i]['id'].") AS F1 
        LEFT JOIN (
        SELECT users_id, message, date FROM post
        WHERE users_id = ".$posts[$i]['id']."
        ORDER BY date DESC LIMIT 1
        ) AS P ON P.users_id = ".$posts[$i]['id']."
        JOIN (
        SELECT COUNT(*) AS followed FROM post WHERE users_id = ".$posts[$i]['id']." AND users_id = (SELECT id FROM users WHERE username = '$username')
        ) AS F2";
        $connection = getConnection();
        $stmt = $connection->query($sql); 
        $posts [$i]['getPost'] = $stmt->fetch();
    }
    return $posts;
}

function getAllUser ($filter = null, $user_id) {
    $sql = "SELECT DISTINCT u.*, (IFNULL (f.id_seguidores, 0) > 0) AS seguindo FROM users u LEFT JOIN (SELECT * FROM follow WHERE id_seguidores = $user_id) f ON u.id = f.id_seguido WHERE u.id != $user_id AND u.name != ''";
    if (!is_null($filter)) {
        $sql .= " AND u.name LIKE '%$filter%'"; 
    }
    $connection = getConnection();
    $stmt = $connection->query($sql); 
    $users = $stmt->fetchAll();
    /* for ($i=0; $i<count($users);$i++) {
       $sql = "SELECT followers, 
        IF (
        message IS NULL,
        'This user has no posts',
         message) AS message,
        followed
        FROM (
        SELECT COUNT(*) AS followers FROM follow WHERE id_seguido = ".$users[$i]['id'].") 
        AS F1
        LEFT JOIN (
        SELECT users_id, message
        FROM post
        WHERE users_id = ".$users[$i]['id']."
    
        ORDER BY date DESC
        LIMIT 1
        ) AS P ON P.users_id = ".$users[$i]['id']."
    
        JOIN (
        SELECT COUNT(*) AS followed FROM follow WHERE id_seguidores = ".$users[$i]['id']." AND id_seguido = (SELECT id FROM users WHERE username = '$username')
        ) AS F2"; 
        $connection = getConnection();
        $stmt = $connection->query($sql); 
        $users [$i]['getFollow'] = $stmt->fetch(); 
    } */
    return $users;
}

function writeFollowStatusbyUserID ($changeFollowStatus, $userid) {
    $sql = "SELECT 1 FROM follow WHERE id_seguido = $changeFollowStatus AND id_seguidores = $userid";
    $connection = getConnection(); 
    $stmt = $connection->query($sql);

    if ($stmt->rowCount() > 0){ 
       $sql = "DELETE FROM follow WHERE id_seguido = $changeFollowStatus AND id_seguidores = $userid";
    } else {
        $sql = "INSERT INTO follow (id_seguido, id_seguidores) VALUES ($changeFollowStatus, $userid)";
    };
    $stmt = $connection->query($sql);
} 

function getfollowersWritePag_mp ($user_id) { 
    $sql = "SELECT * FROM(SELECT COUNT(*) AS seguidores FROM follow WHERE id_seguido = $user_id) AS x,(SELECT COUNT(*) AS seguindo FROM follow WHERE id_seguidores = $user_id) AS y";
    $connection = getConnection();
    $stmt = $connection->query($sql);
    return $stmt->fetchAll();
} 

function limitPostbyusername ($username){
   $pagina = (isset($_GET['pagina']))? $_GET['pagina'] : 1;
   $no_of_records_per_page = 5;
   $offset = ($pagina-1) * $no_of_records_per_page; 

   $connection = getConnection();
   $total_pages_sql = "SELECT COUNT(*) FROM post WHERE users_id = $username";
   $stmt = $connection->query($total_pages_sql);

   $total_rows = $stmt->fetch()["COUNT(*)"];
   
   $total_pages = ceil($total_rows / $no_of_records_per_page);
   $sql = "SELECT p.*, u.name, u.username  FROM post p, users u WHERE (p.users_id IN (SELECT id_seguidores FROM follow WHERE users_id = $username) OR p.users_id = $username) AND p.users_id = u.id ORDER BY p.id DESC LIMIT $offset, $no_of_records_per_page";
   $stmt = $connection->query($sql);
   $result = $stmt->fetchAll();
  
   return [ 
    'pagina' => $pagina,
    'total_pages' => $total_pages,
    'posts' => $result 
   ];

}

function limitAllPost ($filter){
    $pagina = (isset($_GET['pagina']))? $_GET['pagina'] : 1;
    $no_of_records_per_page = 5;
    $offset = ($pagina-1) * $no_of_records_per_page; 
 
    $connection = getConnection();
    $total_pages_sql = "SELECT COUNT(*) FROM post";
    $stmt = $connection->query($total_pages_sql);
    $total_rows = $stmt->fetch()["COUNT(*)"];  
    $total_pages = ceil($total_rows / $no_of_records_per_page);
    
    $sql = "SELECT p.*, u.name, u.username FROM post p, users u WHERE p.message LIKE '%$filter%'AND p.users_id = u.id ORDER BY p.id DESC LIMIT $offset, $no_of_records_per_page";
    $stmt = $connection->query($sql);
    $resultTwo = $stmt->fetchAll();
    return [ 
     'pagina' => $pagina,
     'total_pages' => $total_pages,
     'posts' => $resultTwo 
    ];
    
}

function setLikes($user_id, $post_id){ 
    $connection = getConnection();
    $sql = "SELECT COUNT(*) AS contagem FROM likeposts WHERE id_userlikes = $user_id AND id_posts = $post_id";
    $stmt = $connection->query($sql);
    if(!($stmt->fetchAll()[0]['contagem'] > 0)) { 
        $sql = "INSERT INTO likeposts (id_userlikes, id_posts) VALUES ($user_id, $post_id)";
    } else {
        $sql = "DELETE FROM likeposts WHERE id_userlikes = $user_id AND id_posts = $post_id";
    }
    $stmt = $connection->query($sql);
}

function isLiked ($user_id, $post_id){
    $connection = getConnection();
    $sql = "SELECT COUNT(*) AS contagem FROM likeposts WHERE id_userlikes = $user_id AND id_posts = $post_id";
    $stmt = $connection->query($sql);

    return $stmt->fetch()['contagem'];
}