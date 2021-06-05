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

// o parametro verifica se existi algo dentro requisição changeFollowStatus
if((isset($_GET['changeFollowStatus']) && ($_GET['changeFollowStatus']) != null)) { 
    writeFollowStatusbyUserID($_GET['changeFollowStatus'], $user['id']); 
} 


$filter = isset($_GET['searchBox']) ? $_GET['searchBox'] : null;

// box com os perfis dos usuarios do site.
$allUserHTML = '';  
foreach (getAllUser ($filter, $user['id']) as $value) {    
    $allUserHTML .= "           <div class=\"row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative\">\n";
    $allUserHTML .= "               <div class=\"col p-4 d-flex flex-column position-static\">\n";                       
    $allUserHTML .= "                    <h3 id=\"name-profile\" class=\"mb-0\">".$value['name']."</h3>\n";
    $allUserHTML .= "                    <div class=\"mb-1 text-muted\">@".$value['username']."</div>\n";             
    $allUserHTML .= "                     <a href=\"./profiles?changeFollowStatus=".$value['id']."\" id=\"followProfiles\" class=\"stretched-link\"  >".(($value['seguindo']>0)?"Unf":"F")."ollow</a>\n";  
    $allUserHTML .= "                </div>\n";
    $allUserHTML .= "                <div class=\"col-auto d-none d-lg-block\"> </div>\n";
    $allUserHTML .= "           </div>\n\n";
}  


include '../views/pag_profiles.phtml'; 


