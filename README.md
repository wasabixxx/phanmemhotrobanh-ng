
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

## Yêu cầu cơ sở dữ liệu 
- VAI TRÒ, CA LÀM VIỆC KIỂU EMU

## Yêu cầu xử lí ngoại lệ
- CHECK DOANH THU THEO CA ĐƯỢC CHỌN CA TRONG NGÀY NÀO
- CHECK LỢI NHUẬN THEO CA ĐƯỢC CHỌN CA TRONG NGÀY NÀO

## Yêu cầu công nghệ

-   DÙNG LAVAVEL PHP 
-   LƯU TRỮ CODE TRÊN GITHUB (https://github.com/wasabixxx/pmhtBanHang)

## Config database SQL
-- Tạo cơ sở dữ liệu
CREATE DATABASE QuanLyBanHang;
USE QuanLyBanHang;

-- Tạo bảng cho nhân viên với phân quyền
CREATE TABLE nhan_vien (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten NVARCHAR(50),
    tai_khoan VARCHAR(50) UNIQUE,
    mat_khau VARCHAR(255),
    vai_tro ENUM('ADMIN', 'MANAGER', 'STAFF') NOT NULL,
    ca_lam_viec ENUM('SANG', 'CHIEU', 'TOI') NOT NULL
);

-- Tạo bảng sản phẩm trong kho
CREATE TABLE san_pham (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_san_pham NVARCHAR(100) NOT NULL,
    gia_ban DECIMAL(10, 2) NOT NULL,
    gia_nhap DECIMAL(10, 2) NOT NULL,
    so_luong INT DEFAULT 0,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tạo bảng hóa đơn bán hàng
CREATE TABLE hoa_don (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_nhan_vien INT,
    thoi_gian_ban TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tong_tien DECIMAL(10, 2),
    ca_lam_viec ENUM('SANG', 'CHIEU', 'TOI') NOT NULL,
    FOREIGN KEY (id_nhan_vien) REFERENCES nhan_vien(id)
);

-- Tạo bảng chi tiết hóa đơn để lưu thông tin các sản phẩm trong từng hóa đơn
CREATE TABLE chi_tiet_hoa_don (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_hoa_don INT,
    id_san_pham INT,
    so_luong INT,
    gia_ban DECIMAL(10, 2),
    FOREIGN KEY (id_hoa_don) REFERENCES hoa_don(id),
    FOREIGN KEY (id_san_pham) REFERENCES san_pham(id)
);

-- Bảng theo dõi tồn kho
CREATE TABLE ton_kho (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_san_pham INT,
    so_luong INT,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_san_pham) REFERENCES san_pham(id)
);

-- Tạo bảng doanh thu để lưu doanh thu theo ca và ngày
CREATE TABLE doanh_thu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ngay DATE NOT NULL,
    ca_lam_viec ENUM('SANG', 'CHIEU', 'TOI') NOT NULL,
    tong_doanh_thu DECIMAL(10, 2),
    tong_loi_nhuan DECIMAL(10, 2)
);

-- Bảng phân quyền (cho phép ADMIN, MANAGER và STAFF truy cập các chức năng tương ứng)
CREATE TABLE phan_quyen (
    vai_tro ENUM('ADMIN', 'MANAGER', 'STAFF') PRIMARY KEY,
    quan_ly_tai_khoan BOOLEAN DEFAULT FALSE,
    quan_ly_kho BOOLEAN DEFAULT FALSE,
    xem_doanh_thu BOOLEAN DEFAULT FALSE,
    xem_loi_nhuan BOOLEAN DEFAULT FALSE,
    xem_ton_kho BOOLEAN DEFAULT FALSE
);

-- Thêm dữ liệu phân quyền ban đầu
INSERT INTO phan_quyen (vai_tro, quan_ly_tai_khoan, quan_ly_kho, xem_doanh_thu, xem_loi_nhuan, xem_ton_kho)
VALUES 
    ('ADMIN', TRUE, TRUE, TRUE, TRUE, TRUE),
    ('MANAGER', FALSE, TRUE, TRUE, FALSE, TRUE),
    ('STAFF', FALSE, FALSE, FALSE, FALSE, FALSE);
