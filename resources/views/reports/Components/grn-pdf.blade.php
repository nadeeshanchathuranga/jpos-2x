<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GRN Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        .date-range {
            text-align: right;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        .summary-box strong {
            display: block;
            font-size: 14px;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Goods Received Notes Report</h1>
        <p>Inventory Receipts Summary</p>
    </div>

    <div class="date-range">
        <strong>Period:</strong> {{ $startDate }} to {{ $endDate }}
    </div>

    <div class="summary">
        <div class="summary-box">
            <p>Total GRNs</p>
            <strong>{{ $totals['count'] ?? 0 }}</strong>
        </div>
        <div class="summary-box">
            <p>Total Items</p>
            <strong>{{ $totals['items_count'] ?? 0 }}</strong>
        </div>
        <div class="summary-box">
            <p>Gross Total</p>
            <strong>Rs. {{ $totals['gross_total'] ?? '0.00' }}</strong>
        </div>
        <div class="summary-box">
            <p>Net Total</p>
            <strong>Rs. {{ $totals['net_total'] ?? '0.00' }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>GRN No</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Products</th>
                <th class="text-right">Gross</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Net</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td><strong>{{ $row['grn_no'] }}</strong></td>
                    <td>{{ $row['supplier_name'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>
                        @forelse($row['items'] ?? [] as $item)
                            {{ $item['name'] }} - {{ $item['quantity'] }}<br>
                        @empty
                            —
                        @endforelse
                    </td>
                    <td class="text-right">Rs. {{ number_format($row['gross_total'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($row['line_discount'] + $row['header_discount'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($row['tax_total'], 2) }}</td>
                    <td class="text-right"><strong>Rs. {{ number_format($row['net_total'], 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #999;">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>
