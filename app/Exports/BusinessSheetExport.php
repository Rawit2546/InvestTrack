<?php

namespace App\Exports;

use App\Models\Business;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BusinessSheetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        return Business::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'ชื่อธุรกิจ',
            'วันทำสัญญา',
            'จำนวนเงินลงทุน',
            'เปอร์เซ็นต์ปันผล',
            'รอบวันที่จ่าย',
            'ระยะเวลา',
            'หมายเหตุ',
        ];
    }

    public function map($business): array
    {
        return [
            $business->id,
            $business->name,
            $business->contract_date,
            $business->investment,
            $business->dividend_rate,
            $business->pay_date,
            $business->duration,
            $business->note,
        ];
    }

    public function title(): string
    {
        return 'Businesses';
    }
}
