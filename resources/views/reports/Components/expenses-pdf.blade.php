<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expenses Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Expenses Report</h2>
    <p>Period: {{ $startDate }} to {{ $endDate }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Amount</th>
                <th>Expense Date</th>
                <th>Payment Type</th>
                <th>User</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expensesList as $idx => $e)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $e->title }}</td>
                    <td> {{ $currency ?? '' }} {{ $e->amount }}</td>
                    <td>{{ $e->expense_date }}</td>
                    <td>{{ [0=>'Cash',1=>'Card',2=>'Credit'][$e->payment_type] ?? $e->payment_type }}</td>
                    <td>{{ $e->user->name ?? 'N/A' }}</td>
                    <td>{{ $e->supplier->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Total: {{ $totalExpenses }}</p>
</body>
</html>
