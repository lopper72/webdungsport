# Nghiep vu tra hang

## 1. Muc dich

Chuc nang Tra hang dung de ghi nhan viec khach hang tra lai san pham da mua. Khi lap phieu tra hang, he thong se:

- Ghi nhan lich su tra hang theo khach hang.
- Cong lai ton kho cho san pham duoc tra.
- Giam doanh thu tong.
- Giam doanh thu theo khach hang.
- Giam doanh thu theo hang/san pham.
- Tu dong can tru cong no cua khach hang neu con no.
- Ghi nhan so tien can hoan lai neu tien tra hang lon hon cong no.

## 2. Don hang duoc phep tra

Chi cho phep tra hang voi cac don hang co trang thai:

- Da giao hang (`delivered`)
- Hoan thanh (`completed`)

Khong cho phep tra hang voi cac trang thai:

- Ban nhap (`draft`)
- Cho xu ly (`pending`)
- Da xac nhan (`confirmed`)
- Dang giao (`shipping`)
- Da huy/tu choi (`rejected`)

Ly do: Chi nhung don da giao hoac da hoan thanh moi duoc xem la hang da den tay khach va co the phat sinh tra hang.

## 3. Quy trinh lap phieu tra hang

1. Nhan vien vao menu **Tra hang**.
2. Bam **Them moi**.
3. Chon **Khach hang**.
4. He thong hien thi danh sach san pham khach da mua tu cac don duoc phep tra.
5. Moi dong san pham hien thi:
   - Ma don hang
   - San pham
   - Mau
   - Size
   - Kho
   - So luong da mua
   - So luong da tra truoc do
   - So luong con duoc tra
   - So luong tra lan nay
   - Gia tra
   - Thanh tien
6. Nhan vien nhap **so luong tra** va co the sua **gia tra**.
7. Bam **Tra hang** de luu phieu.

## 4. Cong thuc tinh so luong duoc tra

He thong tinh theo tung dong chi tiet don hang:

```text
So luong con duoc tra = So luong da mua - Tong so luong da tra truoc do
```

Vi du:

```text
Khach mua 10 san pham
Lan truoc da tra 3
Lan nay chi duoc tra toi da 7
```

He thong khong cho phep nhap so luong tra lon hon so luong con duoc tra.

## 5. Cong thuc tinh tien tra hang

```text
Thanh tien tung dong = So luong tra * Gia tra
Tong tien tra hang = Tong thanh tien cac dong
```

Gia tra duoc phep sua, vi thuc te co the xay ra cac truong hop:

- Tra dung gia ban ban dau.
- Tra theo gia thoa thuan.
- Tra mot phan gia tri hang.

## 6. Xu ly ton kho

Khi phieu tra hang duoc luu, ton kho duoc cong lai theo tung dong san pham.

Cong thuc ton kho:

```text
Ton kho = Nhap kho - Ban ra + Tra hang + Chuyen kho vao - Chuyen kho ra
```

Hang tra ve kho theo `warehouse_id` cua dong ban hang.

Vi du:

```text
Don hang ban tu Kho A
Khach tra lai hang
Hang duoc cong lai vao Kho A
```

## 7. Xu ly doanh thu

Khi phat sinh tra hang:

```text
Doanh thu thuc = Doanh thu ban hang - Tong tien tra hang
```

Cac bao cao bi anh huong:

- Bao cao doanh thu tong
- Bao cao doanh thu theo khach hang
- Bao cao doanh thu theo hang
- Bao cao ton kho

## 8. Xu ly cong no va hoan tien

Khi lap phieu tra hang, he thong tu dong xu ly theo thu tu:

1. Lay tong tien tra hang.
2. Kiem tra cong no hien tai cua khach hang.
3. Can tru cong no truoc.
4. Neu tien tra hang lon hon cong no, phan du la tien can hoan lai cho khach.

Cong thuc:

```text
Tien can no = Min(Tong tien tra hang, Cong no hien tai)
Tien can hoan = Tong tien tra hang - Tien can no
```

Vi du 1:

```text
Cong no hien tai: 500.000
Tong tien tra hang: 300.000

=> Can tru cong no: 300.000
=> Tien can hoan: 0
```

Vi du 2:

```text
Cong no hien tai: 500.000
Tong tien tra hang: 800.000

=> Can tru cong no: 500.000
=> Tien can hoan: 300.000
```

Vi du 3:

```text
Cong no hien tai: 0
Tong tien tra hang: 800.000

=> Can tru cong no: 0
=> Tien can hoan: 800.000
```

## 9. Cach can tru cong no

Neu khach hang con nhieu don no, he thong tu dong can tru vao cac don con no theo thu tu cu nhat truoc:

1. Don co ngay dat hang cu hon duoc can tru truoc.
2. Neu don do duoc can het no, trang thai thanh toan chuyen thanh `paid`.
3. Neu chi can mot phan, trang thai thanh toan chuyen thanh `partial`.
4. Neu tien tra hang con du, tiep tuc can sang don no tiep theo.

## 10. Cac truong hop nghiep vu

### 10.1. Tra mot phan

Khach mua 10 san pham, tra 3 san pham.

Ket qua:

- Luu phieu tra voi so luong 3.
- Ton kho tang 3.
- Doanh thu giam theo gia tri 3 san pham.
- Lan sau con duoc tra toi da 7.

### 10.2. Tra toan bo

Khach mua 10 san pham, tra 10 san pham.

Ket qua:

- Luu phieu tra voi so luong 10.
- Ton kho tang 10.
- Doanh thu giam theo gia tri 10 san pham.
- Dong hang do khong con hien trong danh sach duoc tra.

### 10.3. Tra nhieu lan

Khach mua 10 san pham.

```text
Lan 1 tra 2
Lan 2 tra 3
```

Ket qua:

```text
Tong da tra: 5
Con duoc tra: 5
```

### 10.4. Tra hang co sua gia

Khach mua gia 100.000, nhan vien nhap gia tra 90.000.

Ket qua:

- Doanh thu giam 90.000 cho moi san pham tra.
- Khong bat buoc giam theo gia ban goc.

### 10.5. Khach khong co san pham duoc tra

Neu khach chua co don `delivered` hoac `completed`, hoac tat ca san pham da tra het, man hinh se khong co dong nao de tra.

Ket qua:

- Khong tao duoc phieu tra.

### 10.6. Nhap so luong tra vuot qua so luong con duoc tra

Vi du:

```text
Da mua: 5
Da tra: 2
Con duoc tra: 3
Nhan vien nhap tra: 4
```

Ket qua:

- He thong bao loi.
- Khong cho luu phieu.

### 10.7. Don hang bi huy

Don `rejected` khong duoc phep tra hang.

Ly do:

- Don huy khong tinh la ban thanh cong.
- Khong anh huong doanh thu va ton kho ban ra.

## 11. Du lieu luu tru

Phieu tra hang luu tai bang `sales_returns`:

- Ma phieu tra
- Khach hang
- Ngay tra
- Tong tien tra
- Tien can tru cong no
- Tien can hoan
- Ghi chu
- Trang thai

Chi tiet tra hang luu tai bang `sales_return_details`:

- Phieu tra hang
- Don hang goc
- Chi tiet don hang goc
- San pham
- Mau
- Size
- Kho
- So luong tra
- Gia tra
- Thanh tien
- Ghi chu

## 12. Ket qua mong muon

Sau khi hoan tat chuc nang tra hang, he thong dam bao:

- Khong tra vuot so luong da mua.
- Khong tra hang cho don chua giao hoac don da huy.
- Ton kho duoc cong lai dung kho.
- Doanh thu duoc giam dung theo tien tra hang.
- Doanh thu theo khach hang duoc giam dung.
- Doanh thu theo hang duoc giam dung.
- Cong no duoc tu dong can tru.
- Neu can hoan tien, he thong ghi nhan ro so tien can hoan.

