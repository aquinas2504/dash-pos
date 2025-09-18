<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                $q->where('supplier_name', 'LIKE', "%$searchQuery%");
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
            'supplier_name' => 'required|string|max:50|unique:suppliers,supplier_name',
            'supplier_phone' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:20',
            'city' => 'required|string|max:50',
            'address' => 'required|string',
        ]);

        // ambil kode terakhir
        $lastSupplier = Supplier::orderBy('supplier_code', 'desc')->first();
        if ($lastSupplier) {
            // ambil angka di belakang (misal SUPP0005 → 5)
            $lastNumber = (int) substr($lastSupplier->supplier_code, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $supplierCode = 'SUPP' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $data = $request->all();
        $data['supplier_code'] = $supplierCode;

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
            'supplier_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'supplier_name')->ignore($supplier_code, 'supplier_code'),
            ],
            'supplier_phone'  => 'nullable|string|max:50',
            'npwp'            => 'nullable|string|max:20',
            'city'            => 'required|string|max:50',
            'address'         => 'required|string',
        ]);

        $supplier = Supplier::findOrFail($supplier_code);
        $supplier->update([
            'supplier_name'  => $request->supplier_name,
            'supplier_phone' => $request->supplier_phone,
            'npwp'           => $request->npwp,
            'city'           => $request->city,
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
