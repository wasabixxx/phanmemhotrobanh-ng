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

    // Trả về HTML của kết quả tìm kiếm
    if (count($products) > 0) {
        foreach ($products as $product) {
            echo '<tr>
                    <td><img src="' . htmlspecialchars($product['image']) . '" alt="Ảnh sản phẩm" width="50"></td>
                    <td>' . htmlspecialchars($product['product_code']) . '</td>
                    <td>' . htmlspecialchars($product['product_name']) . '</td>
                    <td>' . number_format($product['price_sell']) . ' ₫</td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="' . $product['id'] . '">
                            <input type="hidden" name="product_code" value="' . $product['product_code'] . '">
                            <input type="hidden" name="price_sell" value="' . $product['price_sell'] . '">
                            <input type="number" name="quantity" placeholder="Số lượng" required min="1" max="' . $product['quantity'] . '">
                            <button type="submit" name="add_to_bill">Thêm vào bill</button>
                        </form>
                    </td>
                </tr>';
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

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Staff Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        img { vertical-align: middle; }
    </style>
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
                    $('#search_results').html(data); // Cập nhật nội dung HTML với dữ liệu trả về
                }).fail(function() {
                    console.log('Có lỗi xảy ra trong yêu cầu AJAX.');
                });
            });
        });
    </script>
</head>
<body>
    <h1>Quản Lý Sản Phẩm - Nhân Viên</h1>
    <p>Tên người dùng: <?php echo htmlspecialchars($user['username']); ?></p>
    <p>Ca làm việc: <?php echo htmlspecialchars($shift); ?></p>
    <p>Ngày: <?php echo htmlspecialchars($current_date); ?></p>
    <p>Thời gian: <span id="clock"></span></p>

    <input type="text" id="search_query" placeholder="Tìm sản phẩm" required>
    <div id="search_results"></div>

    <h2>Bill của bạn</h2>
    <table>
        <thead>
            <tr>
                <th>Mã sản phẩm</th>
                <th>Tên sản phẩm</th>
                <th>Giá bán</th>
                <th>Số lượng</th>
                <th>Tổng</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($_SESSION['bill'])): ?>
                <?php foreach ($_SESSION['bill'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo number_format($item['price_sell']); ?> ₫</td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo number_format($item['total']); ?> ₫</td>
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
        <button type="submit" name="pay_bill">Thanh toán</button>
        <button type="submit" name="cancel_bill">Huỷ thanh toán</button>
    </form>
</body>
</html>
