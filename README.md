# English Writing Coach (PHP MVC nhẹ)

Web app PHP giúp học sinh luyện viết tiếng Anh và giáo viên chấm nhanh theo 4 tiêu chí Cambridge (Task, Cohesion, Lexical, Grammar).

## Tính năng chính
- 3 vai trò: `student`, `teacher`, `admin`.
- Student: chọn topic/task, nộp bài, xem auto-feedback, rewrite mode, model essays, common mistakes theo topic, dashboard tiến bộ + lỗi.
- Teacher: quick grading với gợi ý điểm + template comments + featured.
- Admin: quản lý users/topics/tasks.
- Reports: lỗi tổng hợp, xu hướng theo topic; export CSV; trang in A4-friendly.
- Bảo mật: session auth, password_hash, PDO prepared statements, CSRF token, escaping output.

## Cấu trúc
```
app/
  Controllers/
  Core/
  Models/
  Views/
public/index.php
database/schema.sql
database/seed.sql
storage/logs/
.env(.example)
```

## Yêu cầu
- PHP 8.0+
- SQLite3 (mặc định) hoặc MySQL/MariaDB (sửa `.env`)


## Cài đặt siêu nhanh trên host (1 file)
1. Upload toàn bộ source code lên host.
2. Mở trình duyệt tới: `https://your-domain.com/install.php`
3. Kiểm tra các điều kiện môi trường (PHP/PDO/SQLite/quyền ghi).
4. Bấm **Cài đặt ngay** để installer tự: 
   - tạo `.env`
   - tạo DB SQLite
   - import `database/schema.sql` + `database/seed.sql`
   - tạo file khóa `storage/.installed`
5. Đăng nhập tại `/public/login` (hoặc `/login` nếu DocumentRoot đã trỏ vào thư mục `public`) bằng tài khoản demo.

> Lưu ý: Bản one-click hiện tối ưu cho **SQLite** (phù hợp đa số shared host). Nếu bạn bắt buộc dùng MySQL, hãy cấu hình `.env` thủ công và import SQL bằng phpMyAdmin/CLI.

## Cài đặt (Linux/macOS)
1. Clone repo và vào thư mục project.
2. Tạo DB SQLite:
   ```bash
   mkdir -p database
   sqlite3 database/app.db < database/schema.sql
   sqlite3 database/app.db < database/seed.sql
   ```
3. Chạy server:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
4. Mở: `http://localhost:8000/login`

## Cài đặt (Windows)
1. Cài PHP 8.0+ và thêm vào PATH.
2. Tạo DB:
   ```bat
   sqlite3 database\app.db < database\schema.sql
   sqlite3 database\app.db < database\seed.sql
   ```
3. Chạy:
   ```bat
   php -S 0.0.0.0:8000 -t public
   ```

## Tài khoản demo
- admin: `admin@example.com` / `admin123`
- teacher: `teacher@example.com` / `teacher123`
- student: `student@example.com` / `student123`

## Ghi chú
- Nếu dùng MySQL: cập nhật `.env` với `DB_DRIVER=mysql` và thông số DB.
- Logging lỗi tại `storage/logs/app.log`.
