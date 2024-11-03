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
            // Xóa các bản ghi liên quan trong bảng sales
            $ids = implode(',', $product_ids);
            $sql_sales_delete = "DELETE FROM sales WHERE product_id IN ($ids)";
            $conn->query($sql_sales_delete);
            
            // Sau đó xóa sản phẩm
            $sql_products_delete = "DELETE FROM products WHERE id IN ($ids)";
            $conn->query($sql_products_delete);
            
            // Commit transaction
            $conn->commit();
            $success = 'Sản phẩm đã được xóa thành công!';
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $conn->rollback();
            $error = 'Lỗi khi xóa sản phẩm: ' . $e->getMessage();
        }
    } else {
        $error = 'Chưa chọn sản phẩm nào để xóa!';
    }
}

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM products";
$products = $conn->query($sql);
?>

<!-- HTML phần giao diện -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sản Phẩm</title>
</head>
<body>
    <h1>Quản Lý Sản Phẩm</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo $edit_mode ? $edit_product['id'] : ''; ?>">
        <input type="text" name="product_code" placeholder="Mã sản phẩm" required value="<?php echo isset($edit_product) ? $edit_product['product_code'] : ''; ?>">
        <input type="text" name="product_name" placeholder="Tên sản phẩm" required value="<?php echo $edit_mode ? $edit_product['product_name'] : ''; ?>">
        <input type="number" name="quantity" placeholder="Số lượng" required value="<?php echo $edit_mode ? $edit_product['quantity'] : ''; ?>">
        <input type="number" step="0.01" name="price_buy" placeholder="Giá nhập" required value="<?php echo $edit_mode ? $edit_product['price_buy'] : ''; ?>">
        <input type="number" step="0.01" name="price_sell" placeholder="Giá bán" required value="<?php echo $edit_mode ? $edit_product['price_sell'] : ''; ?>">
        <input type="file" name="image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
        <button type="submit" name="<?php echo $edit_mode ? 'update_product' : 'save_product'; ?>"><?php echo $edit_mode ? 'Cập Nhật' : 'Thêm Sản Phẩm'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="manage_products.php">Hủy</a>
        <?php endif; ?>
    </form>

    <h2>Danh Sách Sản Phẩm</h2>
    <form action="" method="POST">
    <table>
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Mã sản phẩm</th>
                <th>Tên sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá mua</th>
                <th>Giá bán</th>
                <th>Hình ảnh</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $products->fetch_assoc()): ?>
            <tr>
                <td><input type="checkbox" name="product_ids[]" value="<?php echo $product['id']; ?>"></td>
                <td><?php echo $product['product_code']; ?></td>
                <td><?php echo $product['product_name']; ?></td>
                <td><?php echo $product['quantity']; ?></td>
                <td><?php echo number_format($product['price_buy']); ?> ₫</td>
                <td><?php echo number_format($product['price_sell']); ?> ₫</td>
                <td><img src="<?php echo $product['image']; ?>" alt="<?php echo $product['product_name']; ?>" style="width: 100px; height: auto;"></td>
                <td>
                    <a href="?edit=<?php echo $product['id']; ?>">Sửa</a>
                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="number" name="quantity" placeholder="Nhập số lượng">
                        <button type="submit" name="add_stock">Nhập hàng</button>
                        <button type="submit" name="remove_stock">Hủy hàng</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <button type="submit" name="delete_products">Xóa sản phẩm đã chọn</button>
    </form>
    
    <script>
        document.getElementById('select-all').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="product_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
</body>
</html>
