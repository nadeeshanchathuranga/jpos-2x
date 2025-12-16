<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4a5568;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-retail {
            background-color: #3b82f6;
            color: white;
        }
        .badge-wholesale {
            background-color: #8b5cf6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report by Type</h1>
    </div>
    
    <div class="date-range">
        Period: {{ $startDate }} to {{ $endDate }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th class="text-right">Sales Count</th>
                <th class="text-right">Gross Total</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Net Total</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesSummary as $sale)
            <tr>
                <td>
                    <span class="badge {{ $sale->type == 1 ? 'badge-retail' : 'badge-wholesale' }}">
                        {{ $sale->type == 1 ? 'Retail' : 'Wholesale' }}
                    </span>
                </td>
                <td class="text-right">{{ $sale->total_sales }}</td>
                <td class="text-right">Rs. {{ number_format($sale->gross_total, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($sale->total_discount, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($sale->net_total, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($sale->total_balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
