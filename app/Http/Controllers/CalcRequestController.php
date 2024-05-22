<?php

namespace App\Http\Controllers;

use App\Models\CalcRequest;
use Illuminate\Http\Request;

class CalcRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CalcRequest::with('selectedPayments');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('updated_at', [$request->start_date, $request->end_date]);
        }

        $calcRequests = $query->paginate($request->get('per_page', 20));

        return response()->json($calcRequests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'iin' => 'required|string',
            'name' => 'required|string',
            'surname' => 'required|string',
            'price' => 'required|integer|min:0',
            'initial_payment' => 'required|integer|min:0',
            'additional_income' => 'nullable|integer|min:0',
            'partner_income' => 'nullable|integer|min:0',
            'children_count' => 'nullable|integer|min:0',
            'ads_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $calcRequest = CalcRequest::create($validated);

        $loanAmount = $validated['price'] - $validated['initial_payment'];
        $terms = [12, 24, 36, 48, 60, 72, 84];
        $creditCalculations = [];
        $years = [
            'Centercredit' => 7, // TODO пепревсти в глобальную переменную
            'Eurasian' => 7,
            'Bereke' => 7,
            'VTB' => 7
        ];

        foreach (['Eurasian','Centercredit', 'Bereke', 'VTB'] as $bank) {
            foreach ($terms as $key => $term) {
                $creditCalculations[$bank][$term] = $this->calculateMonthlyPayment($loanAmount, $term, $bank);
                if  ($key+1 == $years[$bank]) {
                    break;
                }
            }
        }

        $response = [
            'calcRequestId' => $calcRequest['id'],
            'creditCalculations' => $creditCalculations,
        ];

        return response()->json($response, 201);
    }

    private function calculateMonthlyPayment($principal, $term, $bank)
    {
        $annualRates = [
            'Centercredit' => 22.68,
            'Eurasian' => 23.68,
            'Bereke' => 26.80,
            'VTB' => 26.40
        ];

        $annualRatePercent = $annualRates[$bank] ?? 0.0; // Default to zero if the bank is not recognized
        $annualRate = $annualRatePercent / 100; // Convert from percentage to decimal

        $monthlyRate = pow(1 + $annualRate, 1 / 12) - 1;

        $monthlyPayment = ($monthlyRate * $principal) / (1 - pow(1 + $monthlyRate, -$term));

        return round($monthlyPayment, 2);
    }



    public function show(CalcRequest $calcRequest)
    {
        return $calcRequest;
    }

    public function update(Request $request, CalcRequest $calcRequest)
    {
        $validated = $request->validate([
            'phone_number' => 'string',
            'iin' => 'string',
            'name' => 'string',
            'surname' => 'string',
            'price' => 'integer|min:0',
            'initial_payment' => 'integer|min:0',
            'additional_income' => 'nullable|integer|min:0',
            'partner_income' => 'nullable|integer|min:0',
            'children_count' => 'nullable|integer|min:0',
            'ads_id' => 'integer',
            'user_id' => 'integer',
        ]);

        $calcRequest->update($validated);

        return response()->json($calcRequest, 200);
    }

    public function destroy(CalcRequest $calcRequest)
    {
        $calcRequest->delete();

        return response()->noContent();
    }
}
