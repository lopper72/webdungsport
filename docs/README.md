# WebDungSport Documentation

Thu muc nay ghi lai phan tich hien trang cua he thong WebDungSport tai thoi diem tao tai lieu. Muc tieu la giup nguoi moi vao du an nam nhanh kien truc, module chinh, cach chay local va cac diem can than trong codebase.

## Tai lieu

- [Tong quan he thong](./system-overview.md)
- [Kien truc va module](./architecture.md)
- [Cai dat va chay local](./local-development.md)
- [Mo hinh du lieu](./data-model.md)
- [Ban do route](./routes.md)

## Tom tat nhanh

WebDungSport la ung dung Laravel 11 + Livewire 3 cho ban hang/do the thao. He thong gom hai vung chinh:

- Client storefront: trang chu, danh muc, thuong hieu, san pham, gio hang, thanh toan, lich su don hang, lien he.
- Admin back office: dashboard, danh muc, thuong hieu, kho, san pham, nhap/chuyen kho, don hang, thanh toan, slider, audit, user/admin, bao cao, thiet lap he thong.

Stack chinh:

- PHP `^8.2`, Laravel `^11.0`
- Livewire `^3.4`
- MySQL theo `.env.example`
- Vite 5, Tailwind CSS 3, Flowbite
- DomPDF de xuat PDF don hang/bao cao
- OwenIt Laravel Auditing de ghi audit cho mot so model

## Ghi chu hien trang

- `README.md` goc van la README mac dinh cua Laravel, chua phan anh du an.
- Worktree dang co thay doi chua commit o order flow va migration `has_debt`; tai lieu nay chi phan tich, khong thay doi cac file do.
- Mot so chuoi tieng Viet trong source hien thi bi loi encoding khi doc tu terminal; can kiem tra encoding thuc te trong editor truoc khi sua noi dung ngon ngu.
