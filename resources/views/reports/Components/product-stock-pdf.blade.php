<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Stock Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Product Stock Report</h2>
    <p>Date: {{ $reportDate ?? date('Y-m-d') }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Stock</th>
                <th>Retail Price</th>
                <th>Wholesale Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productsStock as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->shop_quantity }}</td>
                    <td>{{ $currency ?? '' }} {{ $p->retail_price }}</td>
                    <td>{{ $currency ?? '' }} {{ $p->wholesale_price }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

