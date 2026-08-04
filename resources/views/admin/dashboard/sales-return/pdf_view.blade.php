<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phieu Tra Hang</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DejaVu+Sans&display=swap');
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            width: 700px;
            margin: 0 auto;
            font-size: 12px;
        }
        .invoice-header {
            text-align: center;
        }
        .invoice-header h2 {
            margin: 12px 0 8px;
        }
        .text-header {
            font-weight: bold;
            font-size: 28px;
        }
        .meta {
            margin-top: 18px;
            line-height: 1.7;
        }
        .invoice-details,
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        .invoice-details th,
        .invoice-details td,
        .summary td {
            border: 1px solid black;
            padding: 7px;
        }
        .invoice-details th {
            text-align: center;
        }
        .summary td {
            text-align: right;
        }
        .summary td:first-child {
            width: 70%;
        }
        .total-cell {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <span class="text-header">Dũng Trần Sport</span><br>
        <span>{{ $system?->website }}</span><br>
        <span>{{ $system?->address }}</span><br>
        <span>Hotline: {{ $system?->phone }}</span>
        <h2>PHIẾU TRẢ HÀNG</h2>
        <span>Mã phiếu: {{ $salesReturn->code }}</span><br>
        <span>Ngày trả: {{ optional($salesReturn->return_date)->format('d/m/Y') }}</span>
    </div>

    <div class="meta">
        <div><b>Khách hàng:</b> {{ $salesReturn->customer?->name }}</div>
        <div><b>Ghi chú:</b> {{ $salesReturn->note ?: '-' }}</div>
    </div>

    <table class="invoice-details">
        <thead>
            <tr>
                <th>TÊN HÀNG HÓA</th>
                <th>MÃ ĐƠN</th>
                <th>SL</th>
                <th>ĐƠN GIÁ</th>
                <th>THÀNH TIỀN</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesReturn->details as $detail)
                <tr>
                    <td>
                        {{ $detail->product?->name }}
                        @if($detail->productDetail?->title)
                            - {{ $detail->productDetail->title }}
                        @endif
                        @if($detail->productSize?->size)
                            - {{ $detail->productSize->size }}
                        @endif
                    </td>
                    <td align="center">{{ $detail->order?->code }}</td>
                    <td align="center">{{ number_format($detail->quantity, 0, ',', '.') }}</td>
                    <td align="right">{{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                    <td align="right">{{ number_format($detail->total_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tbody>
            <tr>
                <td>Tổng số lượng trả:</td>
                <td>{{ number_format($totalQuantity, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Tổng tiền hàng trả:</td>
                <td>{{ number_format($salesReturn->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Cấn trừ công nợ:</td>
                <td>{{ number_format($salesReturn->debt_adjustment_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-cell">
                <td>Tiền trả khách:</td>
                <td>{{ number_format($salesReturn->refund_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
