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
  <link rel="shortcut icon" type="image/png" href="https://icons.veryicon.com/png/System/Small%20%26%20Flat/shop.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <style>
         @media print {
            body * {
                visibility: hidden; /* Ẩn tất cả các phần tử trong trang */
            }
            .print-popup, .print-popup * {
                visibility: visible; /* Chỉ hiển thị phần tử có class 'print-popup' */
            }
            .print-popup {
                position: absolute;
                left: 0;
                top: 0;
            }
            /* Ẩn nút in bill và thanh toán khi in */
            .print-popup .btn-info,
            .print-popup .btn-success {
                display: none;
            }
        }
  </style>
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
