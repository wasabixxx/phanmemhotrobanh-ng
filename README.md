
# Phần mềm Hỗ Trợ Bán Hàng

Kịch bản: phần mềm hỗ trợ bán hàng cho cửa hàng tiện lợi có nhiều ca nhân viên trong ngày



## Phân quyền

ADMIN (**ALL RIGHT**): 
- QUẢN LÍ TÀI KHOẢN NHÂN VIÊN (phân quyền, tạo tài khoản)
- QUẢN LÍ THÊM THÊM ĐỒ VÀO KHO (Thêm vào sản phẩm có sẵn hoặc tạo sản phẩm mới, nhập giá bán và giá nhập)
- CHECK DOANH THU (check theo ca, ngày, 7 ngày gần đây, 30 ngày gần đây, doanh thu tổng)
- CHECK LỢI NHUẬN (check theo ca, ngày, 7 ngày gần đây, 30 ngày gần đây, lợi nhuận tổng) = DOANH THU ĐÃ BÁN ĐƯỢC - GIÁ SẢN PHẨM BÁN RA 
- CHECK HÀNG TỒN KHO

MANAGER:
- QUẢN LÍ THÊM THÊM ĐỒ VÀO KHO (Thêm vào sản phẩm có sẵn hoặc tạo sản phẩm mới, nhập giá nhập)
- CHECK DOANH THU (check theo ca, ngày, 7 ngày gần đây, 30 ngày gần đây, doanh thu tổng)
- CHECK HÀNG TỒN KHO

STAFF:
- TÌM THÔNG TIN SẢN PHẨM THEO SẢN PHẨM THỰC TẾ VÀ NHẬP SỐ LƯỢNG
- NHẬN TIỀN THEO PHẦN MỀM HIỂN THỊ
- CHECK TIỀN THEO CA

## Yêu cầu về các page
- PAGE login
- PAGE admin/manager 
- PAGE nhân viên


## Yêu cầu cơ sở dữ liệu 
- VAI TRÒ, CA LÀM VIỆC KIỂU EMU
- Cần có bảng cho vai trò người dùng và bảng cho ca làm việc, với các quan hệ rõ ràng để quản lý quyền truy cập.

## Yêu cầu xử lí
- DÙNG CHUNG MỘT GIAO DIỆN LOGIN, ĐƯỢC ĐƯA VỀ PAGE TƯƠNG ỨNG VỚI ROLE, RIÊNG admin/manger CHUNG PAGE CHỈ LÀ NỘI DUNG HIỂN THỊ CỦA MANAGER BỊ HẠN CHẾ
- CHECK DOANH THU THEO CA ĐƯỢC CHỌN CA TRONG NGÀY NÀO
- CHECK LỢI NHUẬN THEO CA ĐƯỢC CHỌN CA TRONG NGÀY NÀO

## Yêu cầu về giao diện
- Page Login:
Tạo một form đăng nhập duy nhất cho tất cả người dùng. Sau khi đăng nhập, người dùng sẽ được chuyển đến trang tương ứng dựa trên vai trò của họ.

- Page Admin/Manager:
Dùng chung một giao diện, nhưng nội dung sẽ bị hạn chế cho manager. Nên sử dụng các thẻ điều kiện để kiểm tra vai trò và hiển thị nội dung phù hợp.
Page Nhân Viên:

- Tạo giao diện cho nhân viên để tìm kiếm sản phẩm và ghi nhận doanh thu theo ca.

## Yêu cầu công nghệ

-   DÙNG LAVAVEL PHP 
-   LƯU TRỮ CODE TRÊN GITHUB (https://github.com/wasabixxx/pmhtBanHang)
-   HỆ QUẢN TRỊ CƠ SỞ DỮ LIỆU SQLITE