<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PartnerPayoutSheetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        return Transaction::where('type', 'ปันผลหุ้นส่วน')->with('partner')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'วันที่',
            'BusinessID',
            'PartnerID',
            'ชื่อผู้ลงทุน',
            'จำนวนปันผล',
            'หมายเหตุ',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->date,
            $transaction->business_id,
            $transaction->partner_id,
            $transaction->partner ? $transaction->partner->name : '',
            $transaction->amount,
            $transaction->note,
        ];
    }

    public function title(): string
    {
        return 'Partner_Payouts';
    }
}
