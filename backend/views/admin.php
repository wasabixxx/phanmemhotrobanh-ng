<?php
session_start();
require_once 'connect_db.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: login");
    exit;
}
$role_id = $_SESSION['role_id'] ?? null;

// Lựa chọn thời gian
$time_frame = $_GET['time_frame'] ?? 'day';

// Biến lưu dữ liệu biểu đồ
$profit_data = [];
$revenue_data = [];
$profit_labels = [];

// Lấy dữ liệu doanh thu và lợi nhuận theo thời gian
if ($time_frame == 'week') {
    $sql = "SELECT DATE(sale_time) AS date, SUM(profit) AS total_profit, SUM(total) AS total_revenue
            FROM sales 
            WHERE sale_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(sale_time)";
} elseif ($time_frame == 'month') {
    $sql = "SELECT DATE(sale_time) AS date, SUM(profit) AS total_profit, SUM(total) AS total_revenue
            FROM sales 
            WHERE sale_time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            GROUP BY DATE(sale_time)";
} else { // day
    $sql = "SELECT HOUR(sale_time) AS hour, SUM(profit) AS total_profit, SUM(total) AS total_revenue
            FROM sales 
            WHERE DATE(sale_time) = CURDATE()
            GROUP BY HOUR(sale_time)";
}

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $profit_data[] = (float)$row['total_profit'];
    $revenue_data[] = (float)$row['total_revenue'];
    $profit_labels[] = ($time_frame == 'day') ? $row['hour'] . ':00' : $row['date'];
}

// Lấy dữ liệu năng suất nhân viên
$sql_productivity = "
    SELECT u.username, SUM(s.quantity) AS total_quantity
    FROM users u
    JOIN sales s ON u.id = s.user_id
    WHERE s.sale_time >= 
        CASE 
            WHEN '$time_frame' = 'day' THEN CURDATE()
            WHEN '$time_frame' = 'week' THEN DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            WHEN '$time_frame' = 'month' THEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            ELSE CURDATE()
        END
    GROUP BY u.id
";

$result_productivity = $conn->query($sql_productivity);

// Kiểm tra nếu truy vấn không thành công
if (!$result_productivity) {
    die("Lỗi truy vấn năng suất nhân viên: " . $conn->error);
}


$usernames = [];
$quantities = [];
while ($row = $result_productivity->fetch_assoc()) {
    $usernames[] = $row['username'];
    $quantities[] = (int)$row['total_quantity'];
}

$time_frame = in_array($time_frame, ['day', 'week', 'month']) ? $time_frame : 'day';


$conn->close();
?>

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
            <select class="form-select form-select-sm mb-3 mb-md-5" name="time_frame" id="time_frame" style="max-width: 100px;" onchange="this.form.submit()">
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
                  <canvas id="profitRevenueChart" width="400" height="200" ></canvas>
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
        // Dữ liệu biểu đồ lợi nhuận và doanh thu
        const profitLabels = <?php echo json_encode($profit_labels); ?>;
        const profitData = <?php echo json_encode($profit_data); ?>;
        const revenueData = <?php echo json_encode($revenue_data); ?>;

        const ctx1 = document.getElementById('profitRevenueChart').getContext('2d');
        const profitRevenueChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: profitLabels,
                datasets: [
                    {
                        label: 'Doanh Thu',
                        data: revenueData,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    },
                    {
                        label: 'Lợi Nhuận',
                        data: profitData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Thống Kê Doanh Thu và Lợi Nhuận'
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Thời Gian'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Giá Trị (VND)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Dữ liệu biểu đồ năng suất nhân viên
        const usernames = <?php echo json_encode($usernames); ?>;
        const quantities = <?php echo json_encode($quantities); ?>;

        // Tạo màu ngẫu nhiên cho từng cột
        const colors = usernames.map(() => `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.6)`);

        const ctx2 = document.getElementById('productivityChart').getContext('2d');
        const productivityChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: usernames,
                datasets: [
                    {
                        label: 'Sản Lượng',
                        data: quantities,
                        backgroundColor: colors,
                        borderColor: colors.map(color => color.replace('0.6', '1')),
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Năng Suất Nhân Viên'
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Nhân Viên'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Số Lượng'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

    </script>
</body>

</html>