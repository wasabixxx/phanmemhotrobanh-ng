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
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Khởi tạo bill nếu chưa có
if (!isset($_SESSION['bill'])) {
    $_SESSION['bill'] = [];
    $_SESSION['total_bill'] = 0;
}

// Xử lý thêm sản phẩm vào bill
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_bill'])) {
    $quantity = $_POST['quantity'] ?? 0;
    $product_id = $_POST['product_id'];

    // Lấy thông tin sản phẩm
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    // Cập nhật bill trong session
    $_SESSION['bill'][] = [
        'product_code' => $product['product_code'],
        'product_name' => $product['product_name'],
        'price_sell' => $product['price_sell'],
        'quantity' => $quantity,
        'total' => $quantity * $product['price_sell'],
        'price_buy' => $product['price_buy'], // Thêm price_buy vào bill
        'product_id' => $product_id // Thêm product_id vào bill
    ];
    $_SESSION['total_bill'] += $quantity * $product['price_sell'];
}

// Kiểm tra nếu có yêu cầu cập nhật số lượng
if (isset($_POST['update_quantity'])) {
  $product_id = $_POST['product_id'];
  $new_quantity = $_POST['quantity'];

  // Kiểm tra xem số lượng có hợp lệ không
  if (is_numeric($new_quantity) && $new_quantity > 0) {
      // Duyệt qua giỏ hàng và cập nhật số lượng
      foreach ($_SESSION['bill'] as $key => $item) {
          if ($item['product_id'] == $product_id) {
              $_SESSION['bill'][$key]['quantity'] = $new_quantity;
              $_SESSION['bill'][$key]['total'] = $item['price_sell'] * $new_quantity; // Cập nhật tổng tiền
              break;
          }
      }

      // Cập nhật lại tổng tiền của Bill
      $_SESSION['total_bill'] = 0;
      foreach ($_SESSION['bill'] as $item) {
          $_SESSION['total_bill'] += $item['total'];
      }
  }

  // Redirect lại trang Bill
  header("Location: staff"); // Điều hướng lại trang bill.php (hoặc trang hiện tại)
  exit();
}

// Kiểm tra nếu có yêu cầu xóa sản phẩm
if (isset($_POST['remove_product'])) {
  $product_id = $_POST['product_id'];

  // Duyệt qua giỏ hàng và xóa sản phẩm
  foreach ($_SESSION['bill'] as $key => $item) {
      if ($item['product_id'] == $product_id) {
          unset($_SESSION['bill'][$key]); // Xóa sản phẩm khỏi giỏ hàng
          break;
      }
  }

  // Cập nhật lại tổng tiền của Bill
  $_SESSION['total_bill'] = 0;
  foreach ($_SESSION['bill'] as $item) {
      $_SESSION['total_bill'] += $item['total'];
  }

  // Redirect lại trang Bill
  header("Location: staff"); // Điều hướng lại trang bill.php (hoặc trang hiện tại)
  exit();
}

// Xử lý thanh toán
if (isset($_POST['pay_bill'])) {
    if (!empty($_SESSION['bill'])) {
        foreach ($_SESSION['bill'] as $item) {
            // Tính lợi nhuận
            $profit = $item['total'] - ($item['price_buy'] * $item['quantity']);

            // Chèn vào bảng sales
            $sql = "INSERT INTO sales (product_code, user_id, product_id, quantity, total, profit, sale_time)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siiidd", $item['product_code'], $user_id, $item['product_id'], $item['quantity'], $item['total'], $profit);
            $stmt->execute();

            // Cập nhật số lượng sản phẩm
            $sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }
        // Reset bill
        $_SESSION['bill'] = [];
        $_SESSION['total_bill'] = 0;
    } else {
        echo "Bill trống. Không có sản phẩm nào để thanh toán.";
    }
}

// Xử lý huỷ thanh toán
if (isset($_POST['cancel_bill'])) {
    $_SESSION['bill'] = [];
    $_SESSION['total_bill'] = 0;
}

// Tìm kiếm sản phẩm
$products = [];
$search_query = '';
if (isset($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
    $sql = "SELECT * FROM products WHERE product_name LIKE ? OR product_code LIKE ?";
    $search_term = '%' . $search_query . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($product = $result->fetch_assoc()) {
        $products[] = $product;
    }

    // Trả về HTML của kết quả tìm kiếm - live search
    if (count($products) > 0) {
        foreach ($products as $product) {
            echo '<table class="table text-nowrap align-middle mb-0">
                    <tbody class="table-group-divider">
                      <tr>
                        <!-- Cột ảnh sản phẩm -->
                        <td class="text-center align-middle">
                          <img src="' . htmlspecialchars($product['image']) . '" alt="Ảnh sản phẩm" width="50">
                        </td>
                        
                        <!-- Cột mã sản phẩm -->
                        <td class="text-center align-middle fw-medium">' . htmlspecialchars($product['product_code']) . '</td>
                        
                        <!-- Cột tên sản phẩm -->
                        <td class="text-center align-middle fw-medium">' . htmlspecialchars($product['product_name']) . '</td>
                        
                        <!-- Cột giá bán -->
                        <td class="text-center align-middle fw-medium">' . number_format(htmlspecialchars($product['price_sell'])) . ' ₫</td>
                        
                        <!-- Cột thao tác với số lượng và nút thêm vào bill -->
                        <td class="text-center align-middle">
                          <div class="d-flex justify-content-center align-items-center">
                            <form action="" method="POST" class="d-inline-flex align-items-center">
                              <input type="hidden" name="product_id" value="' . htmlspecialchars($product['id']) . '">
                              <input type="hidden" name="product_code" value="' . htmlspecialchars($product['product_code']) . '">
                              <input type="hidden" name="price_sell" value="' . htmlspecialchars($product['price_sell']) . '">

                              <!-- Ô nhập số lượng với kích thước cố định -->
                              <input class="form-control form-control-sm me-2" type="number" name="quantity" placeholder="Số lượng" required min="1" max="' . htmlspecialchars($product['quantity']) . '" style="width: 80px;">

                              <!-- Nút thêm vào bill -->
                              <button class="btn btn-success btn-sm" type="submit" name="add_to_bill">Thêm vào bill</button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>';
        }
    } else {
        echo '<tr><td colspan="5">Không tìm thấy sản phẩm nào.</td></tr>';
    }
    exit(); // Dừng thực hiện để không hiển thị phần HTML khác
}

// Tính toán thời gian và xác định ca làm việc
date_default_timezone_set('Asia/Ho_Chi_Minh');
$current_time = date('H:i:s');
$current_date = date('Y-m-d');
$shift = ($current_time >= '07:00:00' && $current_time < '12:00:00') ? 'Ca sáng' : (($current_time >= '12:00:00' && $current_time < '17:00:00') ? 'Ca chiều' : (($current_time >= '17:00:00' && $current_time < '22:00:00') ? 'Ca tối' : 'Ngoài giờ làm việc'));
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;
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
          <a href="login" class="text-nowrap logo-img">
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
              <a class="sidebar-link active" href="staff" aria-expanded="false">
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
              <!-- card-1 -->
              <div class="card">
                <div class="card-body">
                  <p>Tên người dùng: <?php echo htmlspecialchars($user['username']); ?></p>
                  <p>Ca làm việc: <?php echo htmlspecialchars($shift); ?></p>
                  <p>Ngày: <?php echo htmlspecialchars($current_date); ?></p>
                  <p>Thời gian: <span id="clock"></span></p>

                  <input class="form-control" type="text" id="search_query" placeholder="Tìm sản phẩm" required>
                  <div id="search_results" class="table-responsive" ></div>
                </div>
              </div>
              <!-- card-2  -->
              <div class="card">
                  <h2 class="card-title fw-semibold mb-4">Bill của bạn</h2>
                  <div class="card-body">
                    <table class="table text-nowrap align-middle mb-0">
                      <thead>
                        <tr class="border-2 border-bottom border-primary border-0">
                          <th scope="col" class="text-center">Mã sản phẩm</th>
                          <th scope="col" class="text-center">Tên sản phẩm</th>
                          <th scope="col" class="text-center">Giá bán</th>
                          <th scope="col" class="text-center">Số lượng</th>
                          <th scope="col" class="text-center">Tổng</th>
                          <th scope="col" class="text-center">Hành động</th> <!-- Cột hành động -->
                        </tr>
                      </thead>
                      <tbody class="table-group-divider">
                        <?php if (!empty($_SESSION['bill'])): ?>
                          <?php foreach ($_SESSION['bill'] as $key => $item): ?>
                            <tr>
                              <td scope="row" class="text-center fw-medium"><?php echo htmlspecialchars($item['product_code']); ?></td>
                              <td scope="row" class="text-center fw-medium"><?php echo htmlspecialchars($item['product_name']); ?></td>
                              <td scope="row" class="text-center fw-medium"><?php echo number_format($item['price_sell']); ?> ₫</td>
                              <td scope="row" class="text-center fw-medium">
                                <!-- Input để sửa số lượng -->
                                <form action="" method="POST">
                                  <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required>
                                  <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                  <button type="submit" name="update_quantity" class="btn btn-warning btn-sm">Cập nhật</button>
                                </form>
                              </td>
                              <td scope="row" class="text-center fw-medium"><?php echo number_format($item['total']); ?> ₫</td>
                              <td class="text-center">
                                <!-- Nút xóa sản phẩm -->
                                <form action="" method="POST">
                                  <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                  <button type="submit" name="remove_product" class="btn btn-danger btn-sm">Xóa</button>
                                </form>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                          <tr>
                            <td colspan="4">Tổng cộng:</td>
                            <td><?php echo number_format($_SESSION['total_bill']); ?> ₫</td>
                          </tr>
                        <?php else: ?>
                          <tr>
                            <td colspan="5">Bill trống.</td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>

                    <form action="" method="POST">
                      <button class="btn btn-primary m-1" type="button" id="open-payment-modal">Tiến hành thanh toán</button>
                      <button class="btn btn-danger m-1" type="submit" name="cancel_bill">Huỷ thanh toán</button>
                    </form>
                  </div>
                </div>


    <!-- Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content print-popup"> <!-- Thêm class 'print-popup' -->
          <div class="modal-header">
            <h5 class="modal-title" id="paymentModalLabel">Thông tin thanh toán</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p><strong>Tên cửa hàng:</strong> Khánh Tâm Thiện Đức Dương Mart</p>
            <p><strong>Ngày thanh toán:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            <h6>THÔNG TIN MUA HÀNG:</h6>
            
            <table class="table text-nowrap align-middle mb-0">
              <thead>
                <tr class="border-2 border-bottom border-primary border-0">
                  <th scope="col" class="text-center">Mã sản phẩm</th>
                  <th scope="col" class="text-center">Tên sản phẩm</th>
                  <th scope="col" class="text-center">Giá bán</th>
                  <th scope="col" class="text-center">Số lượng</th>
                  <th scope="col" class="text-center">Tổng</th>
                </tr>
              </thead>
              <tbody class="table-group-divider">
                <?php if (!empty($_SESSION['bill'])): ?>
                  <?php foreach ($_SESSION['bill'] as $item): ?>
                    <tr>
                      <td scope="row" class="text-center fw-medium"><?php echo htmlspecialchars($item['product_code']); ?></td>
                      <td scope="row" class="text-center fw-medium"><?php echo htmlspecialchars($item['product_name']); ?></td>
                      <td scope="row" class="text-center fw-medium"><?php echo number_format($item['price_sell']); ?> ₫</td>
                      <td scope="row" class="text-center fw-medium"><?php echo htmlspecialchars($item['quantity']); ?></td>
                      <td scope="row" class="text-center fw-medium"><?php echo number_format($item['total']); ?> ₫</td>
                    </tr>
                  <?php endforeach; ?>
                  <tr>
                    <td colspan="4">Tổng cộng:</td>
                    <td><?php echo number_format($_SESSION['total_bill']); ?> ₫</td>
                  </tr>
                <?php else: ?>
                  <tr>
                    <td colspan="5">Bill trống.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>

            <!-- QR code -->
            <div class="text-center my-3">
            <img src="https://img.vietqr.io/image/MB-446619999-print.png?amount=<?php echo $_SESSION['total_bill']; ?>&addInfo=Thanh toan Vippro mart<?php echo urlencode(date('d/m/Y H:i:s')); ?>&accountName=NGUYEN%20NGOC%20KHANH" alt="QR Code" width="350" height="auto">
            </div>

            <!-- Nút "In bill" và "Thanh toán" -->
            <div class="d-flex justify-content-between">
              <button class="btn btn-info" id="print-bill">1. In bill</button>
              <form action="staff" method="post">
                <button class="btn btn-success" type="submit" name="pay_bill">2. Thanh toán</button>
              </form>
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
    <!-- jQuery (version 3.6.0 as an example) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5+5hb7O7/LL6ELzXe8n+2RmgKczH/29bo0wOnc5L2" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
        function updateClock() {
            const now = new Date();
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: 'Asia/Ho_Chi_Minh' };
            document.getElementById('clock').textContent = now.toLocaleTimeString('vi-VN', options);
        }

        setInterval(updateClock, 1000);
        updateClock();

        // Live search
        $('#search_query').on('input', function() {
            const query = $(this).val();
            if (query.trim() === "") {
            $('#search_results').html('<tr><td colspan="5">Vui lòng nhập từ khóa tìm kiếm.</td></tr>');
            return;
            }
            $.post('', { search_query: query }, function(data) {
            $('#search_results').html(data); // Update HTML content with returned data
            }).fail(function() {
            console.log('Có lỗi xảy ra trong yêu cầu AJAX.');
            });
        });
        });
    </script>
    <script>
      document.getElementById('open-payment-modal').addEventListener('click', function() {
        var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
      });

      document.getElementById('print-bill').addEventListener('click', function() {
        window.print(); // In toàn bộ nội dung của pop-up
      });
    </script>

</body>

</html>