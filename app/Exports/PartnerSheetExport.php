<?php

namespace App\Exports;

use App\Models\Partner;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PartnerSheetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        return Partner::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'BusinessID',
            'ชื่อผู้ร่วมลงทุน',
            'จำนวนเงินลงทุน',
            'เปอร์เซ็นต์ปันผล',
            'หมายเหตุ',
        ];
    }

    public function map($partner): array
    {
        return [
            $partner->id,
            $partner->business_id,
            $partner->name,
            $partner->amount,
            $partner->div_rate,
            '', // No note column in partners table
        ];
    }

    public function title(): string
    {
        return 'Partners';
    }
}
