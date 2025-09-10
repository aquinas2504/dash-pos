<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShippingController extends Controller
{

    public function index()
    {
        $shippings = Shipping::query();

        $shippingspagination = $shippings->paginate(10);

        return view('Pages.Shipping.index', compact('shippings', 'shippingspagination'));
    }

    public function create()
    {
        return view('Pages.Shipping.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_name' => 'required',
            'address' => 'required',
        ]);

        // Simpan dulu untuk dapat ID
        $shipping = Shipping::create([
            'shipping_name' => $request->shipping_name,
            'address' => $request->address,
            'shipping_code' => 'temp', // isi sementara
        ]);

        // Generate shipping_code dari accessor
        $shipping->shipping_code = $shipping->id_shipping;
        $shipping->save();

        return redirect()->route('shippings.index')->with('success', 'Data pengiriman berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $shipping = Shipping::findOrFail($id);
        return view('Pages.Shipping.edit', compact('shipping'));
    }


    public function update(Request $request, Shipping $shipping)
    {
        $request->validate([
            'shipping_name' => 'required',
            'address' => 'required',
        ]);

        $shipping->update($request->only('shipping_name', 'address'));

        return redirect()->route('shippings.index')->with('success', 'Data pengiriman berhasil diperbarui.');
    }

    public function searchShippings(Request $request)
    {
        $query = $request->get('q');
        $shippings = Shipping::where('shipping_name', 'like', '%' . $query . '%')->get();

        return response()->json($shippings);
    }
}
