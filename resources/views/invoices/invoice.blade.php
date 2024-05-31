<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { background-color: #f8f8f8; padding: 10px 20px; display: flex; justify-content: space-between; align-items: baseline; }
        .header-section { padding: 0 10px; }
        .header-section h2 { margin: 0; padding-bottom: 10px; }
        .details { width: 100%; margin: 20px 0; }
        .details table { width: 100%; border-collapse: collapse; }
        .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .footer { text-align: center; padding: 10px 20px; background-color: #f8f8f8; }
        .total-row { font-weight: bold; }
        .invoice-info { text-align: right; }
        .logo { float: right; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-section">
            <h2>Seller Information</h2>
            <p>{{ $invoice->seller_name }}<br>{{ $invoice->seller_email }}</p>
        </div>
        <div class="header-section invoice-info">
            <h2>Invoice #{{ $invoice->id }}</h2>
            <p>Date: {{ now()->format('d/m/Y') }}</p>
        </div>
        <div class="header-section">
            <h2>Customer Information</h2>
            <p>{{ $invoice->username }}<br>{{ $invoice->email }}</p>
        </div>
    </div>
    <div class="details">
        <table>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            @php $total = 0; @endphp
            @php
                $items = is_string($invoice->items) ? json_decode($invoice->items, true) : $invoice->items;
            @endphp
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>${{ number_format($item['price'], 2) }}</td>
                </tr>
                @php $total += $item['price']; @endphp
            @endforeach
            <tr class="total-row">
                <td>Total</td>
                <td>${{ number_format($total, 2) }}</td>
            </tr>
        </table>
    </div>
    <div class="footer">
        <p>Thank you for your business!</p>
    </div>
</div>
</body>
</html>
