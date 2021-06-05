<?php

require_once '../models/users.php';

// verifica se o IP esta na lista de banidos. 
$bannedid = file ('banned-ips.txt'); 

if (in_array($_SERVER['REMOTE_ADDR'], $bannedid)){ 
    header("HTTP/1.1 401 Unauthorized");
    die('Banned!');
} 

// validação do login.
$user = getUserByUsername(verifyAccess()); 
  
// o parametro verifica se existi algo dentro requisição tellAbout
if((isset($_POST['tellAbout']) && ($_POST['tellAbout']) != null)) { 
    writeAboutByUsername($_POST['tellAbout'], $user['username']);
    die;
}

// Carregamento da imagem. Tras a informação sobre o ficheiro onde feito upload.
if (($_SERVER['REQUEST_METHOD'] === 'POST'  && ($_FILES != null))) { 
    $path = './images/profile/'; 
    $hash = md5(uniqid());  
    $date = date('Y-m-d_H_i_s'); 
    $targetFile = sprintf("%s%s_%s_%s", $path, $date, $hash, $_FILES['picture']['name']); 

    $data = file_get_contents($_FILES['picture']['tmp_name']); 
    file_put_contents($targetFile, $data);
    writeImageByUsername ($targetFile, $user['username']); 
    $user = getUserByUsername($user['username']);  
}

// verifica e escreve a data e p post foram escritos no BD.
if( (isset($_POST['writePost'],$_POST['datePost'])) && (($_POST['writePost']) != null) && (($_POST['datePost']) != null)) { // verificando se existi algo dentro da requisição tellAbout, vinda pelo metodo POST recebida pela super global $_POST.
    writeMessageByUsername ($_POST['writePost'], $_POST['datePost'], $user['id']); 
    print_r(json_encode(getPostbyUsername($user['id'])));
    die;
}

$data = limitPostbyusername($user['id']);
extract($data);

//escreve os posts no feed do utilizador.
$allPostsHTML = ''; 
foreach ($posts as $value) {    
    $allPostsHTML .= "           <div class=\"row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative\">\n";
    $allPostsHTML .= "                 <div class=\"col p-4 d-flex flex-column position-static\">\n";                       
    $allPostsHTML .= "                     <h3 id=\"name-profile\" class=\"mb-0\">".$value['name']."</h3>\n";
    $allPostsHTML .= "                     <div class=\"mb-1 text-muted\">".$value['date']."</div>\n";
    $allPostsHTML .= "                     <p class=\"card-text mb-auto\">".$value['message']."</p>\n";
    $allPostsHTML .= "                     <a href=\"#\" class=\"stretched-link\"></a>\n";
    $allPostsHTML .= "                 </div>\n";
    $allPostsHTML .= "                 <div class=\"col-auto d-none d-lg-block\"> </div>\n";
    $allPostsHTML .= "            </div>\n\n"; 
}

$allFollowersHTML = '';
foreach (getfollowersWritePag_mp ( $user['id']) as $value) {   
    $allFollowersHTML .= "                   <h3 class=\"follow\"> | ".$value['seguidores']." Followers | ".$value['seguindo']." Following |</h3>\n";                     
}

$photoPath = "./images/profile/img_profile.png";
if (isset($user['image'])) $photoPath = $user['image'];

$photoName = "User";
if (isset($user['name'])) $photoName = $user['name'];

$photoUsername = "User";
if (isset($user['username'])) $photoUsername = $user['username'];

$aboutUser = "Tell us a little about you...";
if (isset($user['about'])) $aboutUser = $user['about'];


include '../views/pag_my_profile.phtml'; 