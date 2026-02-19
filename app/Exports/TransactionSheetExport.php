<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class TransactionSheetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        return Transaction::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'วันที่ธุรกรรม',
            'ประเภท',
            'จำนวนเงิน',
            'หมายเหตุ',
            'BusinessID',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->date,
            $transaction->type,
            $transaction->amount,
            $transaction->note,
            $transaction->business_id,
        ];
    }

    public function title(): string
    {
        return 'Transactions';
    }
}
