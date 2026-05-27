# Cai Dat Va Chay Local

## Yeu cau

- PHP 8.2+
- Composer
- Node.js va npm
- MySQL
- Extension PHP thong dung cho Laravel va DomPDF

## Cai dat

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Cap nhat `.env` theo database local. `.env.example` dang dung:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webdungsport
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## Database

Tao database MySQL:

```sql
CREATE DATABASE webdungsport CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Chay migration:

```powershell
php artisan migrate
```

Neu can du lieu khoi tao, kiem tra component `App\Livewire\Admin\DataSeeder` va seeder trong `database/seeders` truoc khi chay de tranh ghi de du lieu local.

## Chay ung dung

Terminal 1:

```powershell
php artisan serve
```

Terminal 2:

```powershell
npm run dev
```

Mac dinh Laravel se chay tai `http://127.0.0.1:8000`.

## Build frontend

```powershell
npm run build
```

## Test va format

```powershell
php artisan test
vendor/bin/pint
```

## Cac route can thu nhanh

- Client trang chu: `/`
- Dang nhap client: `/dang-nhap`
- Gio hang: `/gio-hang`
- Admin login: `/admin/login`
- Admin setup lan dau: `/admin/setup`
- Admin dashboard: `/admin`

## Luu y van hanh local

- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` can cac bang tu migration Laravel mac dinh.
- File upload/image co the dung `storage/app/public`; neu can public URL thi chay `php artisan storage:link`.
- PDF dung DomPDF va font trong `storage/fonts`; khi loi font tieng Viet can kiem tra font va encoding.
