<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'form_type' => 'required|string',
            'data' => 'required|array',
        ]);

        $draft = Draft::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'form_type' => $request->form_type,
                'form_id'   => $request->form_id ?? null,
            ],
            [
                'data' => $request->data, // langsung simpan array
                'url'  => $request->url,
            ]
        );

        return response()->json(['message' => 'Draft saved', 'id' => $draft->id]);
    }

    public function loadDraft(Request $request)
    {
        $userId = Auth::id();
        $formType = $request->input('form_type'); // 'purchase_order'
        $draft = Draft::where('user_id', $userId)
                    ->where('form_type', $formType)
                    ->latest()
                    ->first();

        return response()->json($draft);
    }

    public function loadProductPObySO(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));

        $details = SaleDetail::whereIn('id', $ids)
            ->get()
            ->map(function($d) {
                return [
                    'id' => $d->id,
                    'id_product' => $d->id_product,
                    'packing' => $d->packing,
                    'qty_packing' => (int) $d->qty_packing,
                    'unit' => $d->unit,
                    'qty_unit' => (int) $d->quantity
                ];
            });

        return response()->json($details);
    }



    public function index()
    {
        $drafts = Draft::where('user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        return view('Pages.Draft.index', compact('drafts'));
    }

    public function delete($id)
    {
        Draft::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Draft deleted');
    }
}
