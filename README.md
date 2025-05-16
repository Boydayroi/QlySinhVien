# Hệ Thống Quản Lý Sinh Viên

Hệ thống quản lý thông tin sinh viên, khóa học và kết quả học tập.

## Tính năng

- Đăng nhập/Đăng ký
- Quản lý thông tin sinh viên
- Quản lý khóa học
- Theo dõi kết quả học tập
- Đổi mật khẩu
- Quên mật khẩu

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Web server (Apache/Nginx)

## Cài đặt

1. Clone repository:
```bash
git clone https://github.com/your-username/QlySinhVien.git
```

2. Import database:
- Tạo database mới tên `student_management`
- Import file `create_tables.sql`

3. Cấu hình:
- Copy file `config.example.php` thành `config.php`
- Cập nhật thông tin kết nối database trong `config.php`

4. Cấp quyền ghi cho thư mục uploads:
```bash
chmod 777 uploads/
```

## Sử dụng

1. Truy cập trang web qua trình duyệt
2. Đăng nhập với tài khoản mặc định:
   - Username: admin
   - Password: admin123

## Cấu trúc thư mục

```
QlySinhVien/
├── assets/          # CSS, JS, images
├── includes/        # PHP includes
├── uploads/         # Uploaded files
├── config.php       # Database configuration
├── create_tables.sql # Database schema
└── README.md        # Project documentation
```

## Đóng góp

Mọi đóng góp đều được hoan nghênh. Vui lòng tạo issue hoặc pull request.

## Giấy phép

MIT License 