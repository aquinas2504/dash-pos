<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::query();

        $paymentspagination = $payments->paginate(10);

        return view('Pages.payment.index', compact('payments', 'paymentspagination'));
    }

    public function create()
    {
        return view('Pages.Payment.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'rekening_name' => 'required',
            'rekening_number' => 'required',
            'bank_name' => 'required',
        ]);

        $data = $request->all();

        Payment::create($data);

        return redirect()->route('payment.index')->with('success', 'Data Rekening berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        return view('Pages.Payment.edit', compact('payment'));
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'rekening_name' => 'required',
            'rekening_number' => 'required',
            'bank_name' => 'required',
        ]);


        $data = $request->only(['rekening_name', 'rekening_number', 'bank_name']);

        $payment->update($data);

        return redirect()->route('payment.index')->with('success', 'Data berhasil diperbarui.');
    }
}
