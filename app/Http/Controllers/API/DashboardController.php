<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Partner;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary()
    {
        $totalInvestment = Business::sum('investment');
        $totalPartners = Partner::count();
        $totalTransactions = Transaction::count();
        
        // Sum 'amount' for Dividends
        $totalDividends = Transaction::where('type', 'ปันผล')->sum('amount');
        
        // Sum 'amount' for Expenses and Partner Dividends
        $totalExpenses = Transaction::whereIn('type', ['รายจ่าย', 'ปันผลหุ้นส่วน'])->sum('amount');

        return response()->json([
            'total_investment' => $totalInvestment,
            'total_partners' => $totalPartners,
            'total_transactions' => $totalTransactions,
            'total_dividends' => $totalDividends,
            'total_expenses' => $totalExpenses,
        ]);
    }
}
