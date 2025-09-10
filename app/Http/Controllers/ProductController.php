<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Packing;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductPacking;
use App\Http\Controllers\Controller;


class ProductController extends Controller
{

    public function index(Request $request)
    {
        $searchQuery = $request->input('search');

        $query = Product::query();

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('products.product_name', 'LIKE', "%$searchQuery%")
                    ->orWhere('products.product_code', 'LIKE', "%$searchQuery%");
            });
        }

        $productspagination = $query->paginate(10);

        // Tambahkan kolom stok_gudang (qty_ppn + qty_nonppn)
        $productspagination->getCollection()->transform(function ($product) {
            $product->stok_gudang = ($product->qty_ppn ?? 0) + ($product->qty_nonppn ?? 0);
            return $product;
        });
        
        // Tambahkan baris ini untuk mempertahankan parameter pencarian
        $productspagination->appends(['search' => $searchQuery]);

        return view('Pages.Product.index', [
            'productspagination' => $productspagination,
            'searchQuery' => $searchQuery,
        ]);
    }

    public function create()
    {
        $packings = Packing::all();
        $units = Unit::all();

        return view('Pages.Product.create', compact('packings', 'units'));
    }

    public function store(Request $request)
    {

        $customMessages = [
            'product_name.unique' => 'Product name sudah digunakan.',
        ];

        // Validasi input
        $validated = $request->validate([
            'product_code' => 'nullable|string|max:50',
            'product_name' => 'required|string|max:50|unique:products,product_name',
            'description' => 'nullable|string',

            // Optional conversion field validation
            'conversions' => 'nullable|array',
            'conversions.*.packing_id' => 'required_with:conversions.*.conversion_value,conversions.*.unit_id|exists:packings,id',
            'conversions.*.unit_id' => 'required_with:conversions.*.packing_id,conversions.*.conversion_value|exists:units,id',
            'conversions.*.conversion_value' => 'required_with:conversions.*.packing_id,conversions.*.unit_id|integer|min:1',
        ],  $customMessages);

        // Simpan data utama produk
        $product = Product::create([
            'product_code' => $validated['product_code'],
            'product_name' => $validated['product_name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Simpan konversi packing jika ada
        if (!empty($validated['conversions'])) {
            foreach ($validated['conversions'] as $conversion) {
                if (
                    !empty($conversion['packing_id']) &&
                    !empty($conversion['unit_id']) &&
                    !empty($conversion['conversion_value'])
                ) {
                    $product->productPackings()->create([
                        'packing_id' => $conversion['packing_id'],
                        'unit_id' => $conversion['unit_id'],
                        'conversion_value' => $conversion['conversion_value'],
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'New product added successfully.');
    }

    public function edit($id)
    {
        $product = Product::with(['productPackings'])->findOrFail($id);
        $packings = Packing::all();
        $units = Unit::all();

        return view('Pages.Product.edit', compact('product', 'packings', 'units'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'product_code' => 'nullable|string|max:50',
            'product_name' => 'required|string|max:50|unique:products,product_name,' . $product->id,
            'description' => 'nullable|string',
            'conversions' => 'nullable|array',
            'conversions.*.id' => 'nullable|exists:product_packings,id',
            'conversions.*.packing_id' => 'required_with:conversions.*.conversion_value,conversions.*.unit_id|exists:packings,id',
            'conversions.*.unit_id' => 'required_with:conversions.*.packing_id,conversions.*.conversion_value|exists:units,id',
            'conversions.*.conversion_value' => 'required_with:conversions.*.packing_id,conversions.*.unit_id|integer|min:1',
        ]);

        $product->update([
            'product_code' => $validated['product_code'],
            'product_name' => $validated['product_name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Simpan/update/hapus productPackings
        $existingIds = $product->productPackings()->pluck('id')->toArray();
        $updatedIds = [];

        if (!empty($validated['conversions'])) {
            foreach ($validated['conversions'] as $conversion) {
                if (!empty($conversion['id'])) {
                    $packing = $product->productPackings()->find($conversion['id']);
                    if ($packing) {
                        $packing->update([
                            'packing_id' => $conversion['packing_id'],
                            'unit_id' => $conversion['unit_id'],
                            'conversion_value' => $conversion['conversion_value'],
                        ]);
                        $updatedIds[] = $packing->id;
                    }
                } else {
                    $new = $product->productPackings()->create([
                        'packing_id' => $conversion['packing_id'],
                        'unit_id' => $conversion['unit_id'],
                        'conversion_value' => $conversion['conversion_value'],
                    ]);
                    $updatedIds[] = $new->id;
                }
            }
        }

        // Hapus yang tidak ada di updatedIds
        $toDelete = array_diff($existingIds, $updatedIds);
        $product->productPackings()->whereIn('id', $toDelete)->delete();

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }




    // Untuk Fitur Search
    public function search(Request $request)
    {
        $query = $request->q;

        $products = Product::where(function ($q) use ($query) {
            $q->where('product_name', 'LIKE', "%$query%")
                ->orWhere('product_code', 'LIKE', "%$query%");
        })
            ->limit(20)
            ->get(['id', 'product_code', 'product_name']);

        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
            ];
        });

        return response()->json($products);
    }

    // BUAT SO
    public function getPackingOptions($productId)
    {
        return response()->json([
            'all_packings' => Packing::all(['id as packing_id', 'packing_name']),
            'all_units' => Unit::all(['id as unit_id', 'unit_name']),
            'product_packings' => ProductPacking::with(['packing', 'unit'])
                ->where('product_id', $productId)
                ->get()
                ->map(function ($pp) {
                    return [
                        'packing_id' => $pp->packing_id,
                        'unit_id' => $pp->unit_id,
                        'conversion_value' => $pp->conversion_value
                    ];
                }),
        ]);
    }
}
