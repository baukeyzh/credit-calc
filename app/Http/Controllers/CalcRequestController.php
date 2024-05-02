<?php

namespace App\Http\Controllers;

use App\Models\CalcRequest;
use Illuminate\Http\Request;

class CalcRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(CalcRequest::all());
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
            'Halyk' => 5, // TODO пепревсти в глобальную переменную
            'Eurasian' => 6,
            'Bereke' => 5
        ];

        foreach (['Halyk', 'Eurasian', 'Bereke'] as $bank) {
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
        switch ($bank) {
            case 'Halyk':
                $annualRate = 0.003;  // Пример процентной ставки

                break;
            case 'Eurasian':
                $annualRate = 0.21;   // TODO пепревсти в глобальную переменную
                break;
            case 'Bereke':
                $annualRate = 0.09;
                break;
        }

        $monthlyRate = $annualRate / 12;
        $monthlyPayment = ($monthlyRate * $principal) / (1 - pow(1 + $monthlyRate, -$term));

        return round($monthlyPayment);
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
