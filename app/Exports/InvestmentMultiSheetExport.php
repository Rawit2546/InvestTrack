<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvestmentMultiSheetExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new BusinessSheetExport(),      // Sheet: Businesses
            new PartnerSheetExport(),       // Sheet: partner
            new TransactionSheetExport(),   // Sheet: Transactions
            new PartnerPayoutSheetExport(), // Sheet: Partner_Payouts
        ];
    }
}