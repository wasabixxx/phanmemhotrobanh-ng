Để triển khai dự án phần mềm hỗ trợ bán hàng theo yêu cầu của bạn, dưới đây là các bước chính và những điểm cần chú ý:

### 1. **Xây Dựng Cơ Sở Dữ Liệu**
   - **Tạo bảng Users**: Bao gồm các trường `id`, `username`, `password`, `role_id`, `shift_id`, và các thông tin cần thiết khác như tên nhân viên, số điện thoại, email.
   - **Tạo bảng Roles**: Để lưu các quyền của người dùng, gồm các trường `id`, `name` (`admin`, `manager`, `staff`).
   - **Tạo bảng Shifts**: Để quản lý các ca làm việc, gồm các trường `id`, `name`, `start_time`, `end_time`.
   - **Tạo bảng Products**: Để lưu thông tin sản phẩm, gồm các trường như `id`, `name`, `price`, `cost_price`, `quantity`, `created_at`, `updated_at`.
   - **Tạo bảng Sales**: Để lưu thông tin bán hàng, bao gồm các trường `id`, `product_id`, `user_id`, `shift_id`, `quantity`, `total_price`, `profit`, `created_at`.
   - **Quan hệ**:
     - Users liên kết với Roles và Shifts (quan hệ 1-n).
     - Sales liên kết với Products, Users, và Shifts.

### 2. **Triển Khai Controller và Middleware**
   - **AuthController**: Xử lý đăng nhập, đăng xuất, phân quyền người dùng.
   - **AdminController**: Quản lý tài khoản nhân viên, phân quyền, thêm sản phẩm và kiểm tra báo cáo doanh thu, lợi nhuận.
   - **ManagerController**: Quản lý kho và kiểm tra doanh thu.
   - **StaffController**: Tìm kiếm sản phẩm và ghi nhận doanh thu.
   - **Middleware**: Tạo middleware để kiểm tra vai trò của người dùng, chuyển hướng đến các trang tương ứng sau khi đăng nhập.

### 3. **Giao Diện**
   - **Trang Đăng Nhập (Login Page)**: Tạo form đăng nhập duy nhất, chuyển hướng đến trang tương ứng với vai trò của người dùng.
   - **Trang Admin/Manager**:
     - Dùng chung một giao diện, nhưng dùng điều kiện để hiển thị nội dung hạn chế cho Manager.
     - Chức năng kiểm tra doanh thu, lợi nhuận theo ngày, theo ca làm việc.
     - Quản lý kho và thêm sản phẩm.
   - **Trang Nhân Viên**:
     - Cho phép nhân viên tìm kiếm sản phẩm và nhập số lượng bán.
     - Hiển thị tổng tiền theo ca.
  
### 4. **Các Tính Năng Xử Lý**
   - **Phân Quyền Role-Based Access**: Dùng các điều kiện để hiển thị nội dung cho từng vai trò.
   - **Kiểm tra Doanh Thu và Lợi Nhuận**: Cung cấp bộ lọc cho phép chọn ca làm việc và khoảng thời gian cụ thể (ngày, 7 ngày, 30 ngày gần đây).
   - **Quản Lý Ca Làm Việc**: Hiển thị báo cáo theo ca đã chọn, ghi nhận tổng tiền bán được và lợi nhuận theo từng ca.
  
### 5. **Sử Dụng Công Nghệ**
   - **PHP**: Dùng PHP cho toàn bộ back-end và boostrap vaf 
   - **MySQL**: Kết nối với cơ sở dữ liệu MySql, lưu trữ file `.sqlite` trong thư mục dự án.
   - **GitHub**: Cập nhật và lưu trữ code trên GitHub để theo dõi và quản lý phiên bản dự án tại [link repo](https://github.com/wasabixxx/pmhtBanHang).

### 6. **Triển Khai và Kiểm Thử**
   - **Kiểm thử phân quyền và giao diện**: Kiểm tra từng trang xem các nội dung có hiển thị đúng theo vai trò hay không.
   - **Kiểm tra báo cáo doanh thu và lợi nhuận**: Xem dữ liệu có hiển thị đúng theo thời gian và ca làm việc đã chọn không.
   - **Ghi nhận và theo dõi lỗi**: Đảm bảo các tính năng hoạt động ổn định, xử lý các lỗi phát sinh và cập nhật code trên GitHub.


   
<?php
// Lấy URL từ query string
$request = $_GET['url'] ?? '';

// Nếu không có yêu cầu, hiển thị trang mặc định
if ($request === '') {
    require 'backend/views/default.php'; // Trang mặc định
} else {
    // Xử lý routing cho các yêu cầu khác
    switch ($request) {
        case 'about':
            require 'views/about.php';
            break;
        case 'contact':
            require 'views/contact.php';
            break;
        default:
            require 'views/404.php'; // Trang không tìm thấy
    }
}

