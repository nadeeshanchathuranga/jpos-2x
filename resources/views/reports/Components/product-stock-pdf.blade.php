<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product Stock Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-date {
            text-align: center;
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #4a5568;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-in-stock {
            background-color: #48bb78;
            color: white;
        }
        .status-low-stock {
            background-color: #ed8936;
            color: white;
        }
        .status-out-of-stock {
            background-color: #f56565;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Product Stock Report</h1>
    </div>
    
    <div class="report-date">
        Report Date: {{ $reportDate }}
    </div>
    
    <table>
        <thead>
            <tr>
                
                <th>Product Name</th>
                
                <th class="text-right">Current Stock</th>
                <th class="text-right">Retail Price</th>
                <th class="text-right">Wholesale Price</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productsStock as $product)
            @php
                $status = $product->qty == 0 ? 'Out of Stock' : ($product->qty < 10 ? 'Low Stock' : 'In Stock');
                $statusClass = $product->qty == 0 ? 'status-out-of-stock' : ($product->qty < 10 ? 'status-low-stock' : 'status-in-stock');
            @endphp
            <tr>
                 
                <td>{{ $product->name }}</td>
              
                <td class="text-right">{{ $product->qty }}</td>
                <td class="text-right">Rs. {{ number_format($product->retail_price, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($product->wholesale_price, 2) }}</td>
                <td class="text-center">
                    <span class="status {{ $statusClass }}">{{ $status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
