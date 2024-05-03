<?php

namespace App\Http\Controllers;

use App\Models\SelectedPayment;
use Illuminate\Http\Request;

class SelectedPaymentController extends Controller
{
    public function index()
    {
        return SelectedPayment::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'calc_request_id' => 'required|integer',
            'month_count' => 'required|integer',
            'money_per_month' => 'required|integer'
        ]);

        $selectedPayment = SelectedPayment::create($validated);
        return response()->json($selectedPayment, 201);
    }

    public function show($id)
    {
        return SelectedPayment::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'calc_request_id' => 'required|integer',
            'month_count' => 'required|integer',
            'money_per_month' => 'required|integer'
        ]);

        $selectedPayment = SelectedPayment::findOrFail($id);
        $selectedPayment->update($validated);
        return response()->json($selectedPayment, 200);
    }

    public function destroy($id)
    {
        $selectedPayment = SelectedPayment::findOrFail($id);
        $selectedPayment->delete();
        return response()->json(null, 204);
    }
}
