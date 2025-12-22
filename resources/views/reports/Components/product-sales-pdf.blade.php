<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Product Sales Report</h1>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Barcode</th>
                <th class="text-right">Sales Qty</th>
                <th class="text-right">Sales Amount</th>
                <th class="text-right">Returns Qty</th>
                <th class="text-right">Returns Amount</th>
                <th class="text-right">Net Qty</th>
                <th class="text-right">Net Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productSalesReport as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['barcode'] }}</td>
                <td class="text-right">{{ $item['sales_quantity'] }}</td>
                <td class="text-right">{{ $currency ?? '' }}  {{ $item['sales_amount'] }}</td>
                <td class="text-right">{{ $item['returns_quantity'] }}</td>
                <td class="text-right">{{ $currency ?? '' }}  {{ $item['returns_amount'] }}</td>
                <td class="text-right">{{ $item['net_sales_quantity'] }}</td>
                <td class="text-right">{{ $currency ?? '' }}  {{ $item['net_sales_amount'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
