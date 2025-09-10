<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // $admin = auth('admin')->user();

        $searchQuery = $request->input('search');

        $query = Customer::query();

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('customer_code', 'LIKE', "%$searchQuery%")
                    ->orWhere('customer_name', 'LIKE', "%$searchQuery%");
            });
        }

        $customers = $query->paginate(10);

        return view('Pages.Customer.index', compact('customers', 'searchQuery'));
    }

    public function create()
    {
        return view('Pages.Customer.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required|string|max:50|unique:customers,customer_code',
            'customer_name' => 'required|string|max:50',
            'customer_email' => 'required|email|max:50',
            'customer_phone' => 'required|string|max:50',
            'npwp' => 'required|string|max:20',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:50',
            'address' => 'required|string',
        ]);

        $data = $request->all();

        Customer::create($data);

        return redirect()->route('customers.create')->with('success', 'New customer added successfully.');
    }

    public function edit($customer_code)
    {
        $customer = Customer::findOrFail($customer_code);
        return view('Pages.Customer.edit', compact('customer'));
    }

    public function update(Request $request, $customer_code)
    {
        $request->validate([
            'customer_name'   => 'required|string|max:255',
            'customer_email'  => 'required|email',
            'customer_phone'  => 'required',
            'npwp'            => 'required|string',
            'city'            => 'required|string',
            'country'         => 'required|string',
            'address'         => 'required|string',
        ]);

        $customer = Customer::findOrFail($customer_code);
        $customer->update([
            'customer_name'  => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'npwp'           => $request->npwp,
            'city'           => $request->city,
            'country'        => $request->country,
            'address'        => $request->address,
            'status'         => $request->status ?? $customer->status, // kalau mau bisa update status juga
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil diupdate.');
    }


    public function searchCustomers(Request $request)
    {
        $query = $request->get('q');
        $customers = Customer::where('customer_name', 'like', '%' . $query . '%')->get();

        return response()->json($customers);
    }
}
