<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Expense::with(['user:id,name', 'supplier:id,name'])
            ->select('title', 'amount', 'remark', 'expense_date', 'payment_type', 'user_id', 'supplier_id', 'reference')
            ->whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->orderBy('expense_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
             
            'Title',
            'Amount (Rs.)',
            'Payment Type',
            'Supplier',
            'User',
            'Reference',
            'Remark',
            'Expense Date',
        ];
    }

    public function map($row): array
    {
        $paymentTypes = [0 => 'Cash', 1 => 'Card', 2 => 'Credit'];
        
        return [
            
            $row->title,
            number_format($row->amount, 2),
            $paymentTypes[$row->payment_type] ?? 'Unknown',
            $row->supplier->name ?? 'N/A',
            $row->user->name ?? 'N/A',
            $row->reference ?? 'N/A',
            $row->remark ?? '',
            $row->expense_date,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
