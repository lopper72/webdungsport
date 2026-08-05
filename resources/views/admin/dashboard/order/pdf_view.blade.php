<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn Bán Hàng</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DejaVu+Sans&display=swap');
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            width: 700px;
            margin: 0 auto;
        }
        .invoice-header {
            text-align: center;
            display: flex;
            justify-content: center;
            flex-direction: column;
        }
        .invoice-header h2 {
            margin: 5px 0;
        }
        .invoice-details, .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .invoice-details th, .invoice-details td, .summary th, .summary td {
            border: 1px solid black;
            padding: 8px;
           
        }
        .summary {
            margin-top: 20px;
        }
        .summary td {
            text-align: right;
        }
        .total-cell {
            font-weight: bold;
        }
        .text-header{
            font-weight: bold;
            font-size: 30px;
        }
        .rowitem tr{
            height:22px;
        }
        .summary tr{
            height:22px;
        }

    </style>
</head>
<body>

    <div class="invoice-header">
        <span class="text-header">Dũng Trần Sport</span><br>
        <span>{{$title}}</span><br>
        <span>{{$address}}</span><br>
        <span>Hostline: {{$hotline}}</span><br>
        <h2>HÓA ĐƠN BÁN HÀNG</h2>
        <span>{{$date_now}}</span><br>
        <span>{{$time}}</span>
    </div>

    <div>
        <p><b>Khách hàng:</b> {{$username}}</p>
    </div>

    <table class="invoice-details" >
        <thead>
            <tr>
                <th>TÊN HÀNG HÓA</th>
                <th>SL</th>
                @if($has_return_order)
                <th>ĐÃ TRẢ</th>
                <th>CÒN LẠI</th>
                @endif
                <th>ĐƠN GIÁ</th>
                <th>THÀNH TIỀN</th>
            </tr>
        </thead>
        <tbody style="font-size: 9pt;" class="rowitem">
            @foreach ($orderDetails as $detail)
            <tr>
                <td>{{$detail['name']}}</td>
                <td align='center'>{{$detail['quantity']}}</td>
                @if($has_return_order)
                <td align='center'>{{ $returned_quantities[$detail['id']] ?? 0 }}</td>
                <td align='center'>{{ max((int) $detail['quantity'] - (int) ($returned_quantities[$detail['id']] ?? 0), 0) }}</td>
                @endif
                <td align='right'>{{number_format($detail['price'], 0, ',', '.')}}</td>
                <td align='right'>{{number_format($detail['total'], 0, ',', '.')}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <table class="summary">
        <tbody style="font-size: 9pt;">
            <tr>
                <td>Tổng số lượng:</td>
                <td>{{$total_quantity}}</td>
            </tr>
            @if($has_return_order)
            <tr>
                <td>Số lượng đã trả:</td>
                <td>{{$total_returned_quantity}}</td>
            </tr>
            <tr>
                <td>Số lượng còn lại:</td>
                <td>{{$total_remaining_quantity}}</td>
            </tr>
            @endif
            <tr>
                <td>Tổng tiền hàng:</td>
                <td>{{number_format($subtotal_amount, 0, ',', '.')}}</td>
            </tr>

            <tr>
                <td>Chiết khấu ({{$discount_percent}}%):</td>
                <td>{{number_format($discount, 0, ',', '.')}}</td>
            </tr>
            <tr>
                <td>Thành tiền:</td>
                <td>{{number_format($total_amount, 0, ',', '.')}}</td>
            </tr>
            @if($has_return_order)
            <tr>
                <td>Tiền trả hàng đã cấn trừ công nợ:</td>
                <td>{{number_format($return_adjusted, 0, ',', '.')}}</td>
            </tr>
            <tr>
                <td>Số tiền phải trả:</td>
                <td>{{number_format($payable_amount, 0, ',', '.')}}</td>
            </tr>
            @endif
            <tr>
                <td>Đã thanh toán:</td>
                <td>{{number_format($paid_amount, 0, ',', '.')}}</td>
            </tr>
            @if($payment_status === 'paid')
            <tr class="total-cell">
                <td>Còn lại:</td>
                <td>0</td>
            </tr>
            @else
            <tr>
                <td>Nợ phát sinh:</td>
                <td>{{number_format($debt_amount, 0, ',', '.')}}</td>
            </tr>
            <tr class="total-cell">
                <td>Tổng nợ khách hàng:</td>
                <td>{{number_format($total_customer_debt, 0, ',', '.')}}</td>
            </tr>
            @endif
        </tbody>
    </table>


</body>
</html>

