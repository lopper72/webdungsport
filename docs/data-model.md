# Mo Hinh Du Lieu

Tai lieu nay tom tat cac bang va quan he chinh dua tren model va migration hien co.

## Nguoi dung va quyen

- `users`: tai khoan admin va khach hang.
  - Truong chinh: `name`, `email`, `username`, `password`, `role`, `gender`, `phone`, `address`, `is_active`.
  - Role admin duoc middleware kiem tra bang gia tri `system`.
  - `User` co quan he voi `orders` va `contact`.

## Catalog

- `brands`: thuong hieu.
- `categories`: danh muc cha/con qua `parent_id`.
- `products`: san pham chinh.
  - Quan he `category_id`, `brand_id`.
  - Gia co `retail_price`, `wholesale_price`, ve sau co them `sales_price`, `is_sales`, `is_active`.
- `product_detail`: bien the/hinh anh/mau/mo ta ngan cua san pham.
- `product_size`: size cua san pham.
- `slider`/`slides`: noi dung slider trang client.

Quan he trong model:

- `Product hasMany ProductDetail`
- `Product hasMany ProductSize`
- `Product hasOne Brand` qua `brand_id`
- `Product hasOne Category` qua `category_id`
- `Category belongsTo parent Category`, `hasMany children`

## Kho

- `warehouse`: danh sach kho.
- `import_product`: phieu nhap hang, gan `warehouse_id`.
- `import_product_detail`: dong phieu nhap, gan product/detail/size/quantity.
- `transfer_product`: phieu chuyen kho, co `from_warehouse_id`, `to_warehouse_id`.
- `transfer_product_detail`: dong chuyen kho.

Tinh toan hien co:

- `Product::totalImported()` sum quantity tu `import_product_detail`.
- `Product::totalSold()` sum quantity tu `order_detail`, bo qua don bi reject theo join `order_status`.
- `Product::totalAvailable()` = imported - sold.
- `Product::warehouses()` suy ra danh sach kho tu cac phieu nhap.

## Gio hang va don hang

- `cart`: gio hang theo user.
- `cart_item`: dong gio hang, gom product/detail/warehouse/size/uom/quantity/unit price/total.
- `orders`: don hang.
  - Truong chinh: `code`, `user_id`, `payment_method_id`, `payment_status`, `order_date`, `status`, shipping info, subtotal/discount/shipping/grandtotal/total.
  - Migration moi trong worktree them `has_debt`.
- `order_detail`: dong don hang, gom product/detail/warehouse/size/uom/quantity/unit_price/total_amount.
- `payment_methods`: phuong thuc thanh toan.
- `payment_status`: danh muc trang thai thanh toan.
- `order_status`: danh muc/tracking trang thai don.

Quan he trong model:

- `Order hasMany OrderDetail`
- `Order belongsTo User` qua `user_id`
- `Order belongsTo PaymentMethod`
- `Order hasMany OrderStatus`
- `OrderDetail belongsTo Product`, `ProductDetail`, `ProductSize`, `Warehouse`

## Lien he, audit, cau hinh

- `contact`: lien he khach hang, co `is_view`.
- `audits`: du lieu audit tu `owen-it/laravel-auditing`.
- `setup`: danh dau setup he thong da hoan tat.
- `system_information`: thong tin he thong hien thi chung.
- `systems`: cau hinh he thong khac.

## Diem can than khi tao migration moi

- Mot so migration cu khong co `down()` day du.
- Bang `order` duoc rename thanh `orders`, nhung co migration cu tham chieu foreign key den `order`.
- Mot so ten bang khong theo convention Laravel so nhieu, model da set `$table` rieng.
- Nen kiem tra schema thuc te bang `php artisan migrate:fresh` tren database tam truoc khi them migration phu thuoc foreign key.
