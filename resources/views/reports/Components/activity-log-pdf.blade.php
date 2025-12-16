<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Log Report</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            margin: 20px; 
        }
        h2 { 
            color: #333; 
            margin-bottom: 5px; 
            font-size: 18px;
        }
        .period { 
            color: #666; 
            margin-bottom: 10px; 
            font-size: 9px;
        }
        .filters {
            background: #f5f5f5;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            font-size: 9px;
        }
        .filters strong {
            color: #333;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            font-size: 9px;
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 6px; 
            text-align: left; 
        }
        th { 
            background: #e8e8e8; 
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .log-id {
            color: #666;
            font-size: 8px;
        }
        .user-name {
            font-weight: bold;
            color: #2563eb;
        }
        .module {
            background: #dbeafe;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            display: inline-block;
        }
        .action {
            color: #16a34a;
            font-weight: bold;
        }
        .details {
            color: #666;
            font-size: 8px;
            max-width: 300px;
            word-wrap: break-word;
        }
        .timestamp {
            color: #666;
            font-size: 8px;
        }
        .summary-box {
            margin-top: 15px;
            padding: 10px;
            background: #e0f2fe;
            border-radius: 5px;
            font-size: 9px;
        }
        .no-data {
            text-align: center;
            color: #999;
            padding: 40px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h2>📝 Activity Log Report</h2>
    <p class="period">Period: {{ $startDate }} to {{ $endDate }}</p>
    
    <div class="filters">
        <strong>Filters Applied:</strong> 
        User: {{ $selectedUser }} | 
        Module: {{ $selectedModule }}
    </div>
    
    @if($logs->isEmpty())
        <p class="no-data">No activity logs found for the selected filters.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 13%;">Date & Time</th>
                    <th style="width: 12%;">User</th>
                    <th style="width: 12%;">Module</th>
                    <th style="width: 12%;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="log-id">{{ $log['id'] }}</td>
                        <td class="timestamp">{{ $log['created_at'] }}</td>
                        <td class="user-name">{{ $log['user_name'] }}</td>
                        <td><span class="module">{{ $log['module'] }}</span></td>
                        <td class="action">{{ $log['action'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="summary-box">
            <p><strong>Summary:</strong></p>
            <p>Total Activities Logged: <strong>{{ $logs->count() }}</strong></p>
            <p>Date Range: <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong></p>
            @if($selectedUser !== 'All Users')
                <p>Filtered by User: <strong>{{ $selectedUser }}</strong></p>
            @endif
            @if($selectedModule !== 'All Modules')
                <p>Filtered by Module: <strong>{{ $selectedModule }}</strong></p>
            @endif
        </div>
    @endif
</body>
</html>
