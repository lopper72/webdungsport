# Tong Quan He Thong

## Muc dich

WebDungSport la he thong ecommerce va quan tri noi bo cho san pham the thao. Ung dung vua phuc vu khach hang mua san pham, vua ho tro admin quan ly hang hoa, ton kho, don hang, lien he va bao cao.

## Tac nhan chinh

- Khach hang: xem san pham, loc theo collection/thuong hieu, them gio hang, thanh toan, xem thong tin tai khoan va lich su don hang.
- Admin/system user: quan ly catalog, kho, don hang, nguoi dung, cau hinh he thong, audit va bao cao.

## Luong chuc nang chinh

1. Catalog
   - Admin tao thuong hieu, danh muc, san pham.
   - San pham co bien the mau/hinh anh trong `product_detail` va size trong `product_size`.
   - Client hien thi san pham moi, san pham ban chay, spotlight, thuong hieu va collection.

2. Ton kho
   - Admin tao kho trong `warehouse`.
   - Nhap hang qua `import_product` va `import_product_detail`.
   - Chuyen kho qua `transfer_product` va `transfer_product_detail`.
   - Mot so tinh toan ton kho hien nam trong model/component thay vi service rieng.

3. Gio hang va thanh toan
   - Gio hang gom `cart` va `cart_item`.
   - Client chon bien the san pham, kho, size, so luong.
   - Thanh toan tao don hang va chi tiet don hang.

4. Don hang
   - Admin co man hinh danh sach, tao, sua, xem chi tiet.
   - Trang thai don va trang thai thanh toan duoc cap nhat bang modal Livewire.
   - PDF don hang duoc tao qua `PDFController` va DomPDF.

5. Bao cao va cong no
   - Co cac bao cao tong quan, ton kho, doanh thu, thuong hieu, khach hang.
   - Module `Arap` xu ly cac don/cong no lien quan thanh toan.

## Phan quyen va truy cap

- Admin routes nam trong group middleware `AdminAuth`.
- `AdminAuth` yeu cau he thong da setup (`Setup::is_completed`) va user dang nhap co `role === 'system'`.
- Client group dung `CustomerAuth`; middleware nay chu yeu redirect user da dang nhap ra khoi trang dang nhap.
- Middleware web global them localization va share data chung cho view.

## Ngon ngu va giao dien

- Route client dung tieng Viet co dau/khong dau theo slug URL: `/gio-hang`, `/thanh-toan`, `/san-pham-moi`.
- Thu muc `lang/en` va `lang/vn` co ban dich rieng.
- Admin layout nam trong `resources/views/admin/layouts`.
- Client layout nam trong `resources/views/client/layouts`.

## Diem can luu y

- Nhieu controller admin chi tra view, logic nghiep vu nam trong Livewire component.
- Mot so bang dung ten so it hoac ten cu: `product_detail`, `product_size`, `warehouse`, `order_detail`, `transfer_product`.
- Migration ban dau tao bang `order`, sau do rename thanh `orders`. Khi viet migration moi can kiem tra ten bang hien tai.
- Quan he va tinh toan ton kho nam rai rac trong model/component; neu mo rong nghiep vu kho nen can nhac gom ve service de tranh sai lech logic.
