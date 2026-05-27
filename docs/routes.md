# Ban Do Route

Nguon: `routes/web.php`.

## Admin

Tat ca route admin ben duoi nam trong middleware `AdminAuth`, ngoai tru `/admin/login`, `/admin/logout`, `/admin/setup`.

| Method | Path | Name | Xu ly |
| --- | --- | --- | --- |
| GET | `/admin` | `admin` | Dashboard |
| GET | `/admin/seeder` | `admin.seeder` | Livewire DataSeeder |
| GET | `/admin/categories` | `admin.categories` | Danh sach danh muc |
| GET | `/admin/categories/add` | `admin.categories.add` | Them danh muc |
| GET | `/admin/categories/edit/{id}` | `admin.categories.edit` | Sua danh muc |
| GET | `/admin/users` | `admin.users` | Danh sach user |
| GET | `/admin/users/add` | `admin.users.add` | Them user |
| GET | `/admin/users/edit/{id}` | `admin.users.edit` | Sua user |
| GET | `/admin/brands` | `admin.brands` | Danh sach thuong hieu |
| GET | `/admin/brands/add` | `admin.brands.add` | Them thuong hieu |
| GET | `/admin/brands/edit/{id}` | `admin.brands.edit` | Sua thuong hieu |
| GET | `/admin/warehouses` | `admin.warehouses` | Danh sach kho |
| GET | `/admin/warehouses/add` | `admin.warehouses.add` | Them kho |
| GET | `/admin/warehouses/edit/{id}` | `admin.warehouses.edit` | Sua kho |
| GET | `/admin/products` | `admin.products` | Danh sach san pham |
| GET | `/admin/products/add` | `admin.products.add` | Them san pham |
| GET | `/admin/products/edit/{id}` | `admin.products.edit` | Sua san pham |
| POST | `/admin/ck-upload-image` | `admin.ck-upload-image` | Upload anh CKEditor |
| GET | `/admin/inventories` | `admin.inventories` | Trang inventory |
| GET | `/admin/inventories/import-product` | `admin.import-product` | Danh sach phieu nhap |
| GET | `/admin/inventories/import-product/add` | `admin.import-product.add` | Them phieu nhap |
| GET | `/admin/inventories/import-product/edit/{id}` | `admin.import-product.edit` | Sua phieu nhap |
| GET | `/admin/inventories/transfer-warehouse` | `admin.transfer-warehouse` | Danh sach chuyen kho |
| GET | `/admin/inventories/transfer-warehouse/add` | `admin.transfer-warehouse.add` | Them chuyen kho |
| GET | `/admin/inventories/transfer-warehouse/edit/{id}` | `admin.transfer-warehouse.edit` | Sua chuyen kho |
| GET | `/admin/orders` | `admin.orders` | Danh sach don |
| GET | `/admin/orders/add` | `admin.orders.add` | Tao don |
| GET | `/admin/orders/edit/{id}` | `admin.orders.edit` | Sua don |
| GET | `/admin/orders/view/{id}` | `admin.orders.view` | Xem don |
| GET | `/admin/payment-methods` | `admin.payment-methods` | Danh sach phuong thuc thanh toan |
| GET | `/admin/payment-methods/add` | `admin.payment-methods.add` | Them phuong thuc thanh toan |
| GET | `/admin/payment-methods/edit/{id}` | `admin.payment-methods.edit` | Sua phuong thuc thanh toan |
| GET | `/admin/sliders` | `admin.sliders` | Danh sach slider |
| GET | `/admin/sliders/add` | `admin.sliders.add` | Them slider |
| GET | `/admin/sliders/edit/{id}` | `admin.sliders.edit` | Sua slider |
| GET | `/admin/audits` | `admin.audits` | Danh sach audit |
| GET | `/admin/audits/detail/{id}` | `admin.audits.detail` | Chi tiet audit |
| GET | `/admin/administrators` | `admin.administrators` | Danh sach admin |
| GET | `/admin/administrators/add` | `admin.administrators.add` | Them admin |
| GET | `/admin/administrators/edit/{id}` | `admin.administrators.edit` | Sua admin |
| GET | `/admin/systems` | `admin.systems` | Thong tin he thong |
| GET | `/admin/macs` | `admin.macs` | Cau hinh MAC/module |
| GET | `/admin/reports` | `admin.reports` | Bao cao |
| GET | `/admin/reports/prelim/{id}` | `admin.reports.prelim` | Prelim report |
| GET | `/admin/reports/inventory` | `admin.reports.inventory` | Bao cao ton kho |
| GET | `/admin/reports/revenue` | `admin.reports.revenue` | Bao cao doanh thu |
| GET | `/admin/reports/brand` | `admin.reports.brand` | Bao cao thuong hieu |
| GET | `/admin/reports/customer` | `admin.reports.customer` | Bao cao khach hang |
| GET | `/admin/info_admin` | `admin.info_admin` | Thong tin admin |
| GET | `/admin/change_password` | `admin.change_password` | Doi mat khau admin |
| GET | `/admin/contact` | `admin.contact` | Lien he |
| GET | `/admin/contact/edit/{id}` | `admin.contact.edit` | Sua lien he |
| GET | `/admin/araps` | `admin.araps` | Cong no/ARAP |
| GET | `/admin/araps/view/{id}` | `admin.araps.view` | Xem ARAP |
| GET | `/admin/pdf/{id}` | `admin.pdf` | Xuat PDF |

## Admin public/auth

| Method | Path | Name | Xu ly |
| --- | --- | --- | --- |
| GET | `/admin/login` | `admin.login` | Livewire admin login |
| GET | `/admin/logout` | `admin.logout` | Logout admin |
| GET | `/admin/setup` | `admin.setup` | Setup lan dau |
| GET | `/locale/{lang}` | none | Doi ngon ngu |

## Client

Mot so route client nam trong middleware `CustomerAuth`; cac route xem san pham/collection/brand la public.

| Method | Path | Name | Xu ly |
| --- | --- | --- | --- |
| GET | `/` | `index` | Trang chu |
| GET | `/dang-nhap` | `login` | Dang nhap client |
| GET | `/logout` | `logout` | Logout client |
| GET | `/gio-hang` | `cart` | Gio hang |
| GET | `/thong-tin-tai-khoan` | `info_user` | Thong tin tai khoan |
| GET | `/doi-mat-khau` | `change_password` | Doi mat khau |
| GET | `/thanh-toan` | `payment` | Thanh toan |
| GET | `/thong-tin-don-hang` | `order_summaries` | Tong hop don hang |
| GET | `/chi-tiet-don-hang/{id}` | `order_history` | Chi tiet don hang |
| GET | `/lien-he` | `contact` | Lien he |
| GET | `/san-pham/{id}/{slug}` | `product-detail` | Chi tiet san pham |
| GET | `/quen-mat-khau` | `forgot_password` | Quen mat khau |
| GET | `/collection/{slug}` | `collection` | Danh sach theo collection |
| GET | `/spotlight` | `spotlight` | Spotlight |
| GET | `/spotlight/search` | `spotlight.search` | Tim spotlight |
| GET | `/nhan-hang/{slug}` | `brand` | San pham theo thuong hieu |
| GET | `/san-pham-moi` | `product_new` | San pham moi |
| GET | `/san-pham-ban-chay` | `product_best_seller` | San pham ban chay |
