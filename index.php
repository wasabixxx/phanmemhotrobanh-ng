<!--                                 
                                        
           .---.  .--.--.       ,---,.  
          /. ./| /  /    '.   ,'  .'  \ 
      .--'.  ' ;|  :  /`. / ,---.' .' | 
     /__./ \ : |;  |  |--`  |   |  |: | 
 .--'.  '   \' .|  :  ;_    :   :  :  / 
/___/ \ |    ' ' \  \    `. :   |    ;  
;   \  \;      :  `----.   \|   :     \ 
 \   ;  `      |  __ \  \  ||   |   . | 
  .   \    .\  ; /  /`--'  /'   :  '; | 
   \   \   ' \ |'--'.     / |   |  | ;  
    :   '  |--"   `--'---'  |   :   /   
     \   \ ;                |   | ,'    
      '---"                 `----'      
                                    
                                    -->

<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title> PMHT BÁN HÀNG </title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/seodashlogo.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<?php
// Lấy slug từ URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : 'login'; // Mặc định là 'login'

// Xử lý routing dựa trên slug
switch ($slug) {
    case 'login':
        require_once 'backend/views/login.php';
        break;
    case 'admin':
        require_once 'backend/views/admin.php';
        break;
    case 'staff':
        require_once 'backend/views/staff.php';
        break;
    case 'logout':
        require_once 'backend/views/logout.php';
        break;
    case 'user':
        require_once 'backend/views/manage_users.php';
        break;
    case 'product':
        require_once 'backend/views/manage_products.php';
        break;
    case 'history':
        require_once 'backend/views/history.php';
        break;
    default:
        http_response_code(404);
        require_once 'backend/views/404.php';
        break;
}
?>
