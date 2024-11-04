<?php
session_start();
require_once 'connect_db.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    header("Location: login");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$username = "";
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
}

// Xử lý lọc doanh thu
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'today';
$total_revenue = 0;
$total_profit = 0;
$shift_condition = ""; // Khởi tạo điều kiện

// Xác định điều kiện lọc theo vai trò
$current_date = date('Y-m-d');
switch ($filter) {
    case 'today':
        $shift_condition = "sale_time BETWEEN '$current_date 00:00:00' AND '$current_date 23:59:59'";
        break;

    case 'last_7_days':
        $shift_condition = "sale_time BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() + INTERVAL 1 DAY";
        break;

    case 'last_month':
        $shift_condition = "sale_time BETWEEN CURDATE() - INTERVAL 1 MONTH AND CURDATE() + INTERVAL 1 DAY";
        break;
}

// Truy vấn doanh thu
if ($role_id == 3) { // Staff
    // Truy vấn doanh thu cho nhân viên chỉ trong hôm nay
    if ($shift_condition) {
        $sql = "SELECT * FROM sales WHERE user_id = ? AND $shift_condition";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $total_revenue += $row['total'];
        }
    }
} else {
    // Xử lý cho manager và admin
    if ($shift_condition) {
        $sql = "SELECT * FROM sales WHERE $shift_condition";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $total_revenue += $row['total'];
            $total_profit += $row['profit'];
        }
    }
}
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ADMIN</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/seodashlogo.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
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
            <?php if ($role_id == 1 || $role_id == 2): ?>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="admin" aria-expanded="false">
                        <span>
                            <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                        </span>
                        <span class="hide-menu">HELLO</span>
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
            <?php endif; ?>
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
        <h1>Lịch Sử Bán Hàng - <?php echo htmlspecialchars($username); ?></h1>

        <?php if ($role_id == 1): ?>
            <h2>Doanh thu: <?php echo number_format($total_revenue); ?> ₫</h2>
            <h2>Lợi nhuận: <?php echo number_format($total_profit); ?> ₫</h2>
        <?php elseif ($role_id == 2): ?>
            <h2>Doanh thu: <?php echo number_format($total_revenue); ?> ₫</h2>
        <?php elseif ($role_id == 3): ?>
            <h2>Doanh thu hôm nay: <?php echo number_format($total_revenue); ?> ₫</h2>
        <?php endif; ?>

        <form method="POST">
            <label for="filter">Chọn khoảng thời gian:</label>
            <select name="filter" id="filter">
                <?php if ($role_id == 1): ?>
                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                    <option value="last_7_days" <?php echo $filter == 'last_7_days' ? 'selected' : ''; ?>>7 ngày qua</option>
                    <option value="last_month" <?php echo $filter == 'last_month' ? 'selected' : ''; ?>>1 tháng qua</option>
                <?php elseif ($role_id == 2): ?>
                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                    <option value="last_7_days" <?php echo $filter == 'last_7_days' ? 'selected' : ''; ?>>7 ngày qua</option>
                    <option value="last_month" <?php echo $filter == 'last_month' ? 'selected' : ''; ?>>1 tháng qua</option>
                <?php elseif ($role_id == 3): ?>
                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                <?php endif; ?>
            </select>
            <button type="submit">Lọc</button>
        </form>

        <h2>Danh Sách Giao Dịch</h2>
        <table>
            <thead>
                <tr>
                    <th>Mã sản phẩm</th>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Tổng tiền</th>
                    <?php if ($role_id != 3): ?>
                        <th>Lợi nhuận</th>
                    <?php endif; ?>
                    <th>Người bán</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Lấy danh sách giao dịch từ bảng sales
                if ($role_id == 3) {
                    $sql = "SELECT * FROM sales WHERE user_id = ? AND $shift_condition";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                } else {
                    $sql = "SELECT * FROM sales WHERE $shift_condition";
                    $stmt = $conn->prepare($sql);
                }

                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Lấy thông tin sản phẩm từ bảng products
                        $product_id = isset($row['product_id']) ? $row['product_id'] : null;
                        $product_name = '';
                        $product_code = '';
                        if ($product_id !== null) {
                            $product_sql = "SELECT product_name, product_code FROM products WHERE id = ?";
                            $product_stmt = $conn->prepare($product_sql);
                            $product_stmt->bind_param("i", $product_id);
                            $product_stmt->execute();
                            $product_result = $product_stmt->get_result();
                            if ($product_result->num_rows > 0) {
                                $product = $product_result->fetch_assoc();
                                $product_name = isset($product['product_name']) ? $product['product_name'] : '';
                                $product_code = isset($product['product_code']) ? $product['product_code'] : '';
                            }
                        }

                        // Lấy thông tin người bán từ bảng users
                        $seller_id = isset($row['user_id']) ? $row['user_id'] : null;
                        $seller_name = '';
                        if ($seller_id !== null) {
                            $seller_sql = "SELECT username FROM users WHERE id = ?";
                            $seller_stmt = $conn->prepare($seller_sql);
                            $seller_stmt->bind_param("i", $seller_id);
                            $seller_stmt->execute();
                            $seller_result = $seller_stmt->get_result();
                            if ($seller_result->num_rows > 0) {
                                $seller = $seller_result->fetch_assoc();
                                $seller_name = isset($seller['username']) ? $seller['username'] : '';
                            }
                        }

                        // Hiển thị thông tin giao dịch
                        echo '<tr>
                                <td>' . htmlspecialchars($product_code) . '</td>
                                <td>' . htmlspecialchars($product_name) . '</td>
                                <td>' . (isset($row['quantity']) ? htmlspecialchars($row['quantity']) : 'N/A') . '</td>
                                <td>' . (isset($row['total']) ? number_format($row['total']) . ' ₫' : 'N/A') . '</td>';
                        if ($role_id != 3) {
                            echo '<td>' . (isset($row['profit']) ? number_format($row['profit']) . ' ₫' : 'N/A') . '</td>';
                        }
                        echo '<td>' . htmlspecialchars($seller_name) . '</td>
                                <td>' . (isset($row['sale_time']) ? date('d-m-Y H:i:s', strtotime($row['sale_time'])) : 'N/A') . '</td>
                            </tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">Chưa có giao dịch nào.</td></tr>';
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Tổng cộng:</td>
                    <td><?php echo number_format($total_revenue); ?> ₫</td>
                    <?php if ($role_id != 3): ?>
                        <td><?php echo number_format($total_profit); ?> ₫</td>
                    <?php endif; ?>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>