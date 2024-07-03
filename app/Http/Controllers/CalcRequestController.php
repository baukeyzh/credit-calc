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
            // Добавляем конец дня к end_date
            $endDate = new \DateTime($request->end_date);
            $endDate->setTime(23, 59, 59);
            $query->whereBetween('updated_at', [$request->start_date, $endDate]);
        } elseif ($request->has('start_date')) {
            $query->where('updated_at', '>=', $request->start_date);
        } elseif ($request->has('end_date')) {
            // Добавляем конец дня к end_date
            $endDate = new \DateTime($request->end_date);
            $endDate->setTime(23, 59, 59);
            $query->where('updated_at', '<=', $endDate);
        }

        if ($request->has('order_by') && in_array($request->order_by, ['asc', 'desc'])) {
            $query->orderBy('created_at', $request->order_by);
        }

        $calcRequests = $query->get();

        return response()->json($calcRequests);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'iin' => 'required|string',
            'name' => 'required|string',
            'surname' => 'required|string',
            'patronymic' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'initial_payment' => 'required|integer|min:0',
            'additional_income' => 'nullable|integer|min:0',
            'partner_income' => 'nullable|integer|min:0',
            'children_count' => 'nullable|integer|min:0',
            'ads_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $calcRequest = CalcRequest::create($validated);

        $price = $validated['price'];
        $terms = [12, 24, 36, 48, 60, 72, 84];
        $creditCalculations = [];
        $years = [
            'Centercredit' => 7, // TODO пепревсти в глобальную переменную
            'Eurasian' => 7,
            'Bereke' => 7,
            'VTB' => 7
        ];

        $incomePercents = [
            'Centercredit' => 10.00, // TODO пепревсти в глобальную переменную
            'Eurasian' => 15.00,
            'Bereke' => 20.00,
            'VTB' => 25.00
        ];


        $maxAmount = [
            'Centercredit' => 25000000, // TODO пепревсти в глобальную переменную
            'Eurasian' => 13500000,
            'Bereke' => 15000000,
            'VTB' => 8000000,
            //'Freedom' => 25000000
        ];

        foreach (['Eurasian','Centercredit', 'Bereke', 'VTB'] as $bank) {
            $initialPayment = $validated['initial_payment'];

            if ($price - $initialPayment > $maxAmount[$bank]) {
                $initialPayment = $price - $maxAmount[$bank];
            }
            if (floor($initialPayment * 100 / $price) < $incomePercents[$bank]) {
                $initialPayment = $incomePercents[$bank] * $price / 100;
            }
            $loanPrice = $price - $initialPayment;
            foreach ($terms as $key => $term) {
                $creditCalculations[$bank]['monthlyPayments'][$term] = $this->calculateMonthlyPayment($loanPrice, $term, $bank);
                if  ($key+1 == $years[$bank]) {
                    break;
                }
            }
            $creditCalculations[$bank]['incomePercent'] = floor($initialPayment * 100 / $price) ;
            $creditCalculations[$bank]['incomePrice'] = $initialPayment;
            $creditCalculations[$bank]['incomeColor'] = $this->getColorByPercent(floor($initialPayment * 100 / $price));
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

    private function getColorByPercent($percent)
    {
        // Округляем процент до целого числа, чтобы лучше соответствовать условиям
        $percent = round($percent);

        // Используем switch case для определения цвета
        switch (true) {
            case ($percent >= 10 && $percent < 20):
                return "#64bd38"; // Зеленый цвет
            case ($percent >= 20 && $percent < 30):
                return "yellow"; // Желтый цвет
            case ($percent >= 30 && $percent < 50):
                return "orange"; // Оранжевый цвет
            case ($percent >= 50):
                return "red"; // Красный цвет
            default:
                return ""; // В случае, если процент не соответствует заданным интервалам
        }
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
            'patronymic' => 'string',
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
