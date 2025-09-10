<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{

    public function index(Request $request)
    {
        // $admin = auth('admin')->user();

        $searchQuery = $request->input('search');

        $query = Supplier::query();

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('supplier_code', 'LIKE', "%$searchQuery%")
                    ->orWhere('supplier_name', 'LIKE', "%$searchQuery%");
            });
        }

        $suppliers = $query->paginate(10);

        return view('Pages.Supplier.index', compact('suppliers', 'searchQuery'));
    }

    public function create()
    {
        return view('Pages.Supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_code' => 'required|string|max:50|unique:suppliers,supplier_code',
            'supplier_name' => 'required|string|max:50',
            'supplier_email' => 'required|email|max:50',
            'supplier_phone' => 'required|string|max:50',
            'npwp' => 'required|string|max:20',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:50',
            'address' => 'required|string',
        ]);

        $data = $request->all();

        Supplier::create($data);

        return redirect()->route('suppliers.create')->with('success', 'New supplier added successfully.');
    }

    public function edit($supplier_code)
    {
        $supplier = Supplier::findOrFail($supplier_code);
        return view('Pages.Supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $supplier_code)
    {
        $request->validate([
            'supplier_name'   => 'required|string|max:255',
            'supplier_email'  => 'required|email',
            'supplier_phone'  => 'required',
            'npwp'            => 'required|string',
            'city'            => 'required|string',
            'country'         => 'required|string',
            'address'         => 'required|string',
        ]);

        $supplier = Supplier::findOrFail($supplier_code);
        $supplier->update([
            'supplier_name'  => $request->supplier_name,
            'supplier_email' => $request->supplier_email,
            'supplier_phone' => $request->supplier_phone,
            'npwp'           => $request->npwp,
            'city'           => $request->city,
            'country'        => $request->country,
            'address'        => $request->address,
            'status'         => $request->status ?? $supplier->status,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diupdate.');
    }

    public function searchSuppliers(Request $request)
    {
        $query = $request->get('q');
        $suppliers = Supplier::where('supplier_name', 'like', '%' . $query . '%')->get();

        return response()->json($suppliers);
    }
}
