<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Expenses Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            font-size: 9px;
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
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }
        td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f7fafc;
            font-weight: bold;
            border-top: 2px solid #4a5568;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expenses Report</h1>
    </div>
    
    <div class="date-range">
        Period: {{ $startDate }} to {{ $endDate }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Payment</th>
                <th>Supplier</th>
                <th>User</th>
                <th>Reference</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expensesList as $expense)
            @php
                $paymentTypes = [0 => 'Cash', 1 => 'Card', 2 => 'Credit'];
                $paymentType = $paymentTypes[$expense->payment_type] ?? 'Unknown';
            @endphp
            <tr>
                <td>{{ $expense->id }}</td>
                <td>{{ $expense->title }}</td>
                <td>{{ $paymentType }}</td>
                <td>{{ $expense->supplier->name ?? 'N/A' }}</td>
                <td>{{ $expense->user->name ?? 'N/A' }}</td>
                <td>{{ $expense->reference ?? 'N/A' }}</td>
                <td class="text-right">Rs. {{ number_format($expense->amount, 2) }}</td>
                <td class="text-center">{{ $expense->expense_date }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL EXPENSES:</td>
                <td class="text-right">Rs. {{ number_format($totalExpenses, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
