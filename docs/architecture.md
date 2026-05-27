# Kien Truc Va Module

## Stack

- Backend: Laravel 11, PHP 8.2+
- UI tuong tac: Livewire 3
- Frontend build: Vite, Tailwind CSS, Flowbite
- Database: MySQL
- PDF: `barryvdh/laravel-dompdf`
- Audit: `owen-it/laravel-auditing`
- Modal Livewire: `wire-elements/modal`
- Image optimization: Spatie Image Optimizer, Tinify

## Cau truc thu muc quan trong

- `app/Http/Controllers/Admin`: controller route admin, phan lon tra view Blade chua Livewire component.
- `app/Http/Controllers/Client`: controller route storefront.
- `app/Livewire/Admin`: logic form/list/modal cho back office.
- `app/Livewire/Client`: logic gio hang, dang nhap, san pham, thanh toan, tai khoan.
- `app/Models`: Eloquent models anh xa database.
- `resources/views/admin`: Blade layout va page admin.
- `resources/views/client`: Blade layout va page client.
- `resources/views/livewire`: view cua Livewire component.
- `routes/web.php`: toan bo route web hien nam trong mot file.
- `database/migrations`: lich su schema, gom ca cac migration update theo thoi gian.
- `public/library`: CSS/JS/images/fonts tinh dung truc tiep boi giao dien.

## Entry points

- HTTP entry: `public/index.php`
- Laravel bootstrap: `bootstrap/app.php`
- Web routes: `routes/web.php`
- Frontend assets: `resources/css/app.css`, `resources/js/app.js`
- Vite config: `vite.config.js`

## Middleware

- `LocalizationMiddleware`: them vao web middleware stack, xu ly locale.
- `ShareDataMiddleware`: share `system_info`, `categories`, `countCart`, order/contact notifications cho view.
- `AdminAuth`: bao ve admin, yeu cau setup xong va user role `system`.
- `CheckAdminLogin`: chan/redirect trang login admin khi can.
- `CheckSetup`: bao ve trang setup.
- `CustomerAuth`: xu ly trang login client khi user da dang nhap.

## Mau to chuc module admin

Moi module admin thuong gom:

- Controller trong `app/Http/Controllers/Admin/*Controller.php`
- Livewire component trong `app/Livewire/Admin/<Module>`
- Blade page trong `resources/views/admin/dashboard/<module>`
- Livewire view trong `resources/views/livewire/admin/<module>`

Vi du module product:

- `ProductController` dinh tuyen danh sach/them/sua.
- `ListProduct`, `AddProduct`, `EditProduct` xu ly state, validate va persist.
- `resources/views/admin/dashboard/product/*.blade.php` gan layout.
- `resources/views/livewire/admin/product/*.blade.php` hien thi form/list.

## Mau to chuc module client

Client route thuong qua controller de render page, logic dong nam trong Livewire component.

Vi du product detail:

- Route `/san-pham/{id}/{slug}` vao `Client\ProductController@index`.
- Component `App\Livewire\Client\ProductDetail` quan ly bien the san pham, kho, so luong co san.
- View `resources/views/livewire/client/product-detail.blade.php` hien thi UI tuong tac.

## Tai san frontend

- Tailwind quet `resources/**/*.blade.php`, `resources/**/*.js`, Flowbite va view cua Wire Elements Modal.
- `public/library/css/style.css` va `public/library/js/*.js` la tai san tinh co san.
- Vite build hai entry `resources/css/app.css` va `resources/js/app.js`.

## Testing

- Du an cau hinh Pest trong `tests/Pest.php`.
- Hien chi co test mau `ExampleTest`.
- Khi sua nghiep vu don hang/kho, nen bo sung feature test hoac it nhat test component Livewire cho luong tao/sua/xoa.
