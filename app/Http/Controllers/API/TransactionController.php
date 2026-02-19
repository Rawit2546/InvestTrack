<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::with(['business', 'partner'])->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'businessId' => 'required|exists:businesses,id',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'partnerId' => 'nullable|exists:partners,id',
        ]);

        $transaction = Transaction::create([
            'business_id' => $validated['businessId'],
            'partner_id' => $validated['partnerId'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'note' => $validated['note'],
        ]);

        return response()->json(['message' => 'Transaction created successfully', 'data' => $transaction], 201);
    }
    public function show($id)
    {
        return Transaction::with(['business', 'partner'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'businessId' => 'required|exists:businesses,id',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'partnerId' => 'nullable|exists:partners,id',
        ]);

        $transaction = Transaction::findOrFail($id);
        $transaction->update([
            'business_id' => $validated['businessId'],
            'partner_id' => $validated['partnerId'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'note' => $validated['note'],
        ]);

        return response()->json(['message' => 'Transaction updated successfully', 'data' => $transaction]);
    }

    public function destroy($id)
    {
        Transaction::destroy($id);
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
