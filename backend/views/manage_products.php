<?php
session_start();
require_once 'connect_db.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: login");
    exit();
}

// Biến để lưu thông báo lỗi và thành công
$error = '';
$success = '';
$edit_mode = false; // Biến để xác định chế độ chỉnh sửa

// Xử lý thêm hoặc cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_code = $_POST['product_code'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $price_buy = $_POST['price_buy'] ?? '';
    $price_sell = $_POST['price_sell'] ?? '';
    $image = '';

    // Xử lý tải lên hình ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = 'images/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    if (isset($_POST['save_product'])) {
        // Thêm sản phẩm
        $sql = "INSERT INTO products (product_code, product_name, quantity, price_buy, price_sell, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiss", $product_code, $product_name, $quantity, $price_buy, $price_sell, $image);

        if ($stmt->execute()) {
            $success = 'Sản phẩm đã được thêm thành công!';
        } else {
            $error = 'Lỗi khi thêm sản phẩm: ' . $conn->error;
        }
    } elseif (isset($_POST['update_product'])) {
        // Cập nhật sản phẩm
        $product_id = $_POST['product_id'];

        // Nếu không có hình ảnh mới, giữ nguyên hình ảnh cũ
        if (empty($image)) {
            $sql = "UPDATE products SET product_code = ?, product_name = ?, quantity = ?, price_buy = ?, price_sell = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiisi", $product_code, $product_name, $quantity, $price_buy, $price_sell, $product_id);
        } else {
            $sql = "UPDATE products SET product_code = ?, product_name = ?, quantity = ?, price_buy = ?, price_sell = ?, image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiisi", $product_code, $product_name, $quantity, $price_buy, $price_sell, $image, $product_id);
        }

        if ($stmt->execute()) {
            $success = 'Sản phẩm đã được cập nhật thành công!';
        } else {
            $error = 'Lỗi khi cập nhật sản phẩm: ' . $conn->error;
        }
    }
}

// Xử lý nhập và hủy hàng
if (isset($_POST['add_stock'])) {
    $product_id = $_POST['product_id'];
    $quantity_to_add = $_POST['quantity'];

    $sql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity_to_add, $product_id);
    $stmt->execute();
    $success = 'Đã nhập hàng thành công!';
} elseif (isset($_POST['remove_stock'])) {
    $product_id = $_POST['product_id'];
    $quantity_to_remove = $_POST['quantity'];

    $sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity_to_remove, $product_id);
    $stmt->execute();
    $success = 'Đã hủy hàng thành công!';
}

// Xử lý chế độ chỉnh sửa
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $product_id = $_GET['edit'];
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
}

// Xử lý xóa sản phẩm
if (isset($_POST['delete_products'])) {
  if (!empty($_POST['product_ids'])) {
      $product_ids = $_POST['product_ids'];
      
      // Bắt đầu transaction
      $conn->begin_transaction();
      
      try {
          // Chuyển mảng các ID thành chuỗi các ID cách nhau bởi dấu phẩy
          $ids = implode(',', $product_ids);
          
          // Lấy đường dẫn ảnh của các sản phẩm để xóa
          $sql_select_images = "SELECT image FROM products WHERE id IN ($ids)";
          $result = $conn->query($sql_select_images);
          
          // Xóa file ảnh khỏi server nếu có
          while ($row = $result->fetch_assoc()) {
              $image_path = $row['image'];
              if (file_exists($image_path)) {
                  unlink($image_path); // Xóa file ảnh
              }
          }
          
          // Xóa các bản ghi liên quan trong bảng sales
          $sql_sales_delete = "DELETE FROM sales WHERE product_id IN ($ids)";
          $conn->query($sql_sales_delete);
          
          // Sau đó xóa sản phẩm trong bảng products
          $sql_products_delete = "DELETE FROM products WHERE id IN ($ids)";
          $conn->query($sql_products_delete);
          
          // Commit transaction
          $conn->commit();
          $success = 'Sản phẩm và ảnh đã được xóa thành công!';
          
      } catch (Exception $e) {
          // Rollback nếu có lỗi
          $conn->rollback();
          $error = 'Lỗi khi xóa sản phẩm: ' . $e->getMessage();
      }
  } else {
      $error = 'Chưa chọn sản phẩm nào để xóa!';
  }
}

$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;
// Lấy danh sách sản phẩm
$sql = "SELECT * FROM products";
$products = $conn->query($sql);
?>

<!-- HTML phần giao diện -->


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
            <li class="sidebar-item">
              <a class="sidebar-link " href="admin" aria-expanded="false">
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
              <a class="sidebar-link active" href="product" aria-expanded="false">
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
                <!-- card 1 -->
                <div class="card">
                  <h2 class="card-title fw-semibold mb-4">Thêm Sản Phẩm</h2>
                  <div class="card-body">
                    <?php if ($error): ?>
                        <p style="color: red;"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <p style="color: green;"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <input class="mb-3 form-control" type="hidden" name="product_id" value="<?php echo $edit_mode ? $edit_product['id'] : ''; ?>">
                        
                        <!-- Dòng đầu tiên có 2 input -->
                        <div class="row mb-3">
                          <div class="col">
                            <input class="form-control" type="text" name="product_code" placeholder="Mã sản phẩm" required value="<?php echo isset($edit_product) ? $edit_product['product_code'] : ''; ?>">
                          </div>
                          <div class="col">
                            <input class="form-control" type="text" name="product_name" placeholder="Tên sản phẩm" required value="<?php echo $edit_mode ? $edit_product['product_name'] : ''; ?>">
                          </div>
                        </div>

                        <!-- Dòng thứ hai có 2 input -->
                        <div class="row mb-3">
                          <div class="col">
                            <input class="form-control" type="number" name="quantity" placeholder="Số lượng" required value="<?php echo $edit_mode ? $edit_product['quantity'] : ''; ?>">
                          </div>
                          <div class="col">
                            <input class="form-control" type="number" step="0.01" name="price_buy" placeholder="Giá nhập" required value="<?php echo $edit_mode ? $edit_product['price_buy'] : ''; ?>">
                          </div>
                        </div>

                        <!-- Dòng thứ ba có 2 input -->
                        <div class="row mb-3">
                          <div class="col">
                            <input class="form-control" type="number" step="0.01" name="price_sell" placeholder="Giá bán" required value="<?php echo $edit_mode ? $edit_product['price_sell'] : ''; ?>">
                          </div>
                          <div class="col">
                            <input class="form-control" type="file" name="image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                          </div>
                        </div>

                        <button class="btn btn-success m-1 mb-3" type="submit" name="<?php echo $edit_mode ? 'update_product' : 'save_product'; ?>"><?php echo $edit_mode ? 'Cập Nhật' : 'Thêm Sản Phẩm'; ?></button>
                        <?php if ($edit_mode): ?>
                            <a href="product">Hủy</a>
                        <?php endif; ?>
                    </form>
                  </div>
                </div>

                <!-- card 2 -->
                <div class="card">
                  <h2 class="card-title fw-semibold mb-4">Danh Sách Sản Phẩm</h2>
                  <div class="card-body">
                    <form action="" method="POST">
                      <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-3">
                          <thead>
                            <tr class="border-2 border-bottom border-primary border-0">
                              <th scope="col"><input type="checkbox" id="select-all"></th>
                              <th scope="col" class="text-center">Mã sản phẩm</th>
                              <th scope="col" class="text-center">Tên sản phẩm</th>
                              <th scope="col" class="text-center">Tồn kho</th>
                              <th scope="col" class="text-center">Giá mua</th>
                              <th scope="col" class="text-center">Giá bán</th>
                              <th scope="col" class="text-center">Hình ảnh</th>
                              <th scope="col" class="text-center">Hành động</th>
                            </tr>
                          </thead>
                          <tbody class="table-group-divider">
                            <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                              <td scope="row" class="ps-0 fw-medium"><input type="checkbox" name="product_ids[]" value="<?php echo $product['id']; ?>"></td>
                              <td scope="row" class="text-center fw-medium"><?php echo $product['product_code']; ?></td>
                              <td scope="row" class="text-center fw-medium"><?php echo $product['product_name']; ?></td>
                              <td scope="row" class="text-center fw-medium"><?php echo $product['quantity']; ?></td>
                              <td scope="row" class="text-center fw-medium"><?php echo number_format($product['price_buy']); ?> ₫</td>
                              <td scope="row" class="text-center fw-medium"><?php echo number_format($product['price_sell']); ?> ₫</td>
                              <td scope="row" class="text-center fw-medium">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['product_name']; ?>" style="width: 100px; height: auto;">
                              </td>
                              <td scope="row" class="text-center fw-medium">
                                <!-- Nhóm Sửa và Ô Nhập -->
                                <div class="d-flex align-items-center mb-2">
                                  <a class="btn btn-secondary btn-sm me-2" href="?edit=<?php echo $product['id']; ?>">Sửa</a>
                                  <input class="form-control form-control-sm" style="width: 80px;" type="number" name="quantity" placeholder="Số lượng">
                                </div>

                                <!-- Nhóm Nhập hàng và Hủy hàng -->
                                <div class="d-flex">
                                  <form action="" method="POST" style="display:inline-flex;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button class="btn btn-danger btn-sm me-1" type="submit" name="add_stock">Nhập hàng</button>
                                    <button class="btn btn-danger btn-sm" type="submit" name="remove_stock">Hủy hàng</button>
                                  </form>
                                </div>
                              </td>
                            </tr>
                            <?php endwhile; ?>
                          </tbody>
                        </table>
                      </div>

                      <button class="btn btn-danger m-1" type="submit" name="delete_products">Xóa sản phẩm đã chọn</button>
                    </form>
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
  <script>
        document.getElementById('select-all').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="product_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
</body>

</html>