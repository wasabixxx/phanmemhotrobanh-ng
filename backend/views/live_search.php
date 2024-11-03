<?php
session_start();
require_once 'connect_db.php';

if (isset($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
    $sql = "SELECT * FROM products WHERE product_name LIKE ? OR product_code LIKE ?";
    $search_term = '%' . $search_query . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($product = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . htmlspecialchars($product['product_code']) . '</td>
                    <td>' . htmlspecialchars($product['product_name']) . '</td>
                    <td>' . number_format($product['price_sell']) . ' ₫</td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="price_sell" value="' . $product['price_sell'] . '">
                            <input type="number" name="quantity" placeholder="Số lượng" required>
                            <button type="submit" name="calculate">Nhận tiền</button>
                        </form>
                    </td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="5">Không tìm thấy sản phẩm nào.</td></tr>';
    }
}
?>
