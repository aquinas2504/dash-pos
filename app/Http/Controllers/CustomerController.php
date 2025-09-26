<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                $q->where('customer_name', 'LIKE', "%$searchQuery%");
            });
        }

        $customers = $query
        ->orderBy('customer_name', 'asc') // urut A-Z
        ->paginate(10);

        return view('Pages.Customer.index', compact('customers', 'searchQuery'));
    }

    public function create()
    {
        return view('Pages.Customer.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:50|unique:customers,customer_name',
            'customer_phone' => 'nullable|string|max:50',
            'pic' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:20',
            'city' => 'required|string|max:50',
            'address' => 'required|string',
        ]);

        // ambil kode terakhir
        $lastCustomer = Customer::orderBy('customer_code', 'desc')->first();
        if ($lastCustomer) {
            // ambil angka di belakang (misal CUST0005 → 5)
            $lastNumber = (int) substr($lastCustomer->customer_code, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $customerCode = 'CUST' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);


        $data = $request->all();
        $data['customer_code'] = $customerCode;

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
            'customer_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'customer_name')->ignore($customer_code, 'customer_code'),
            ],
            'customer_phone' => 'nullable|string|max:50',
            'pic' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:20',
            'city' => 'required|string|max:50',
            'address' => 'required|string',
        ]);

        $customer = Customer::findOrFail($customer_code);
        $customer->update([
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'pic'            => $request->pic,
            'npwp'           => $request->npwp,
            'city'           => $request->city,
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
