<?php
session_start();
require_once 'connect_db.php';

// <---in ra thông tin đăng nhập--->
// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không có role hợp lệ
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    // Chuyển hướng về trang đăng nhập
    header("Location: login");
    exit; // Dừng thực thi mã sau khi chuyển hướng
}
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;

// Nếu người dùng có role_id là 1 hoặc 2, cho phép truy cập trang

// Xử lý yêu cầu chọn thống kê
$time_frame = isset($_GET['time_frame']) ? $_GET['time_frame'] : 'day';
$profit_data = [];
$profit_labels = [];

// Lấy dữ liệu lợi nhuận theo thời gian
if ($time_frame == 'week') {
    $sql_profit = "SELECT DATE(sale_time) AS date, SUM(profit) AS total_profit 
                   FROM sales 
                   WHERE sale_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                   GROUP BY DATE(sale_time)";
} elseif ($time_frame == 'month') {
    $sql_profit = "SELECT DATE(sale_time) AS date, SUM(profit) AS total_profit 
                   FROM sales 
                   WHERE sale_time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                   GROUP BY DATE(sale_time)";
} else { // day
    $sql_profit = "SELECT HOUR(sale_time) AS hour, SUM(profit) AS total_profit 
                   FROM sales 
                   WHERE DATE(sale_time) = CURDATE()
                   GROUP BY HOUR(sale_time)";
}

$result_profit = $conn->query($sql_profit);
while ($row = $result_profit->fetch_assoc()) {
    $profit_data[] = (float)$row['total_profit'];
    if ($time_frame == 'day') {
        $profit_labels[] = $row['hour'] . ':00'; // Định dạng giờ
    } else {
        $profit_labels[] = $row['date'];
    }
}

// Lấy dữ liệu năng suất nhân viên
$sql_productivity = "
    SELECT u.username, SUM(s.quantity) AS total_quantity
    FROM users u
    JOIN sales s ON u.id = s.user_id
    GROUP BY u.id
";
$result_productivity = $conn->query($sql_productivity);

$usernames = [];
$quantities = [];
while ($row = $result_productivity->fetch_assoc()) {
    $usernames[] = $row['username'];
    $quantities[] = (int)$row['total_quantity'];
}

$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ADMIN</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/seodashlogo.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <style>
    .small-select {
    max-width: 100px; /* Chiều rộng của select */
    /* Bạn có thể thêm các thuộc tính khác nếu cần */
    }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="./index.html" class="text-nowrap logo-img">
            <img src="https://icons.veryicon.com/png/System/Small%20%26%20Flat/shop.png" alt="" style="height:50px ; width: auto;" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">Chào mừng, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link active" href="admin" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Thống kê</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">QUẢN LÍ ADMIN</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="product" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Quản lí hàng</span>
              </a>
            </li>
            <?php if ($role_id == 1): ?>
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-6" class="fs-6"></iconify-icon>
                    <span class="hide-menu">User manager</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="user" aria-expanded="false">
                        <span>
                        <iconify-icon icon="solar:user-plus-rounded-bold-duotone" class="fs-6"></iconify-icon>
                        </span>
                        <span class="hide-menu">Quản lí tài khoản</span>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4" class="fs-6"></iconify-icon>
              <span class="hide-menu">Tài chính</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="history" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:sticker-smile-circle-2-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Check doanh thu</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="staff" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:danger-circle-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Trang bán hàng</span>
              </a>
            </li>
          </ul>
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                <i class="ti ti-bell-ringing"></i>
                <div class="notification bg-primary rounded-circle"></div>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img src="assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="logout" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <form method="GET">
            <label class="card-title fw-semibold" for="time_frame">Chọn Thời Gian:</label>
            <select class="form-select form-select-sm mb-3 mb-md-5 small-select" name="time_frame" id="time_frame" onchange="this.form.submit()">
                <option value="day" <?php echo ($time_frame == 'day') ? 'selected' : ''; ?>>Ngày</option>
                <option value="week" <?php echo ($time_frame == 'week') ? 'selected' : ''; ?>>Tuần</option>
                <option value="month" <?php echo ($time_frame == 'month') ? 'selected' : ''; ?>>Tháng</option>
            </select>
          </form>
          <div class="row">
            <div class="col-lg-5">
              <!-- card1 -->
              <div class="card">
                <h2 class="card-title fw-semibold mb-4">Thống Kê Lợi Nhuận</h2>
                <div class="card-body">
                  <canvas id="profitChart" width="400" height="200" ></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <!-- card2  -->
              <div class="card">
                <h2 class="card-title fw-semibold mb-4">Biểu Đồ Năng Suất Nhân Viên</h2>
                <div class="card-body">
                  <canvas id="productivityChart" width="400" height="200"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
        const ctxProfit = document.getElementById('profitChart').getContext('2d');
        const profitChart = new Chart(ctxProfit, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($profit_labels); ?>,
                datasets: [{
                    label: 'Lợi Nhuận',
                    data: <?php echo json_encode($profit_data); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        title: {
                            display: true,
                            text: time_frame === 'day' ? 'Giờ trong Ngày' : 'Ngày'
                        }
                    }
                }
            }
        });
    </script>
    <script>
        const ctxProductivity = document.getElementById('productivityChart').getContext('2d');
        const productivityChart = new Chart(ctxProductivity, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($usernames); ?>,
                datasets: [{
                    label: 'Số Lượng Bán',
                    data: <?php echo json_encode($quantities); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>