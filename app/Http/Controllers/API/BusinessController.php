<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    public function index()
    {
        return Business::with('partners')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'investment' => 'required|numeric',
            'dividendRate' => 'nullable|string',
            'contractDate' => 'required|date',
            'payDate' => 'nullable|string',
            'duration' => 'nullable|string',
            'note' => 'nullable|string',
            'partners' => 'nullable|array',
            'partners.*.name' => 'required|string',
            'partners.*.amount' => 'required|numeric',
            'partners.*.div' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
           $business = Business::create([
    'name' => $validated['name'],
    'investment' => $validated['investment'],
    'dividend_rate' => $validated['dividendRate'] ?? null,
    'contract_date' => $validated['contractDate'],
    'pay_date' => $validated['payDate'] ?? null,
    'duration' => $validated['duration'] ?? null,
    'note' => $validated['note'] ?? null,
]);

            if (!empty($validated['partners'])) {
                foreach ($validated['partners'] as $partnerData) {
                    $business->partners()->create([
                        'name' => $partnerData['name'],
                        'amount' => $partnerData['amount'],
                        'div_rate' => $partnerData['div'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Business created successfully', 'data' => $business], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Business Creation Error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'Error creating business',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        return Business::with('partners')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'investment' => 'required|numeric',
            'dividendRate' => 'nullable|string',
            'contractDate' => 'required|date',
            'payDate' => 'nullable|string',
            'duration' => 'nullable|string',
            'note' => 'nullable|string',
            'partners' => 'nullable|array',
            'partners.*.id' => 'nullable|exists:partners,id',
            'partners.*.name' => 'required|string',
            'partners.*.amount' => 'required|numeric',
            'partners.*.div' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $business = Business::findOrFail($id);
            $business->update([
                'name' => $validated['name'],
                'investment' => $validated['investment'],
                'dividend_rate' => $validated['dividendRate'] ?? null,
                'contract_date' => $validated['contractDate'],
                'pay_date' => $validated['payDate'] ?? null,
                'duration' => $validated['duration'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            // Handle Partners: Sync strategy (Delete missing, Update existing, Create new)
            if (isset($validated['partners'])) {
                $inputPartnerIds = array_filter(array_column($validated['partners'], 'id'));
                // Delete partners not in the input list
                $business->partners()->whereNotIn('id', $inputPartnerIds)->delete();

                foreach ($validated['partners'] as $p) {
                    if (isset($p['id'])) {
                        // Update existing
                        $business->partners()->where('id', $p['id'])->update([
                            'name' => $p['name'],
                            'amount' => $p['amount'],
                            'div_rate' => $p['div'] ?? null,
                        ]);
                    } else {
                        // Create new
                        $business->partners()->create([
                            'name' => $p['name'],
                            'amount' => $p['amount'],
                            'div_rate' => $p['div'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Business updated successfully', 'data' => $business->load('partners')]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Business Update Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating business',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $business = Business::findOrFail($id);
        $business->delete();
        return response()->json(['message' => 'Business deleted successfully']);
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InvestmentMultiSheetExport, 'investment_data_' . date('Y-m-d_H-i-s') . '.xlsx');
    }
}
