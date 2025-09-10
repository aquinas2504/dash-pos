<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\SuratJalan;
use App\Models\SaleInvoice;
use App\Models\ReturPurchase;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateController extends Controller
{
    // PO Normal
    public function generatePdf($order_number)
    {
        $purchase = Purchase::with(['supplier', 'purchaseDetail.saleDetail.product', 'purchaseDetail.product'])
            ->where('order_number', $order_number)
            ->firstOrFail();

        // Gabungkan data purchaseDetail berdasarkan product_id, packing, unit
        $groupedDetails = collect($purchase->purchaseDetail)
            ->groupBy(function ($item) {
                // fallback ke saleDetail->id_product kalau id_product null
                $productId = $item->id_product ?? $item->saleDetail->id_product;

                return $productId . '|' . $item->packing . '|' . $item->unit;
            })
            ->map(function ($items) {
                $first = $items->first();
                $qty_packing = $items->sum('qty_packing');
                $qty_unit = $items->sum('qty_unit');
                $total = $items->sum('total');

                return (object)[
                    'product' => $first->product,
                    'saleDetail' => $first->saleDetail,
                    'product_name' => $first->saleDetail->product->product_name ?? $first->product->product_name ?? '-',
                    'packing' => $first->packing,
                    'unit' => $first->unit,
                    'qty_packing' => $qty_packing,
                    'qty_unit' => $qty_unit,
                    'price' => $first->price,
                    'discount' => $first->discount,
                    'total' => $total,
                ];
            })->values(); // reset index

        // replace original purchaseDetail with grouped version
        $purchase->purchaseDetail = $groupedDetails;

        $pdf = Pdf::loadView('Generate.purchase', compact('purchase'))->setPaper('A4', 'portrait');
        return $pdf->stream("PO_{$purchase->order_number}.pdf");
    }

    // grouped PO
    public function generatePdf2($order_number)
    {
        $purchase = Purchase::with([
            'supplier',
            'purchaseDetail.saleDetail.product',
            'purchaseDetail.saleDetail.sale.customer',
            'purchaseDetail.product'
        ])->where('order_number', $order_number)->firstOrFail();

        // Kelompokkan detail berdasarkan customer (jika ada), jika tidak -> 'Tanpa Customer'
        $groupedByCustomer = $purchase->purchaseDetail->groupBy(function ($detail) {
            return optional(optional($detail->saleDetail)->sale->customer)->customer_code ?? 'Tanpa Customer';
        });

        // Untuk setiap group customer, gabungkan item yang punya product+packing+unit yang sama
        $groupedDetails = $groupedByCustomer->map(function ($details, $customerCode) {
            return $details->groupBy(function ($item) {
                // Ambil product id yang valid: pertama dari purchase_detail, fallback ke sale_detail
                $productId = $item->id_product ?? optional($item->saleDetail)->id_product;

                // Pastikan key tidak null — jika null, buat fallback unik berbasis id detail untuk mencegah blending
                $productKey = $productId ? (string)$productId : 'no-product-' . ($item->id ?? uniqid());

                $packing = $item->packing ?? '';
                $unit = $item->unit ?? '';

                return implode('|', [$productKey, $packing, $unit]);
            })->map(function ($items) {
                $first = $items->first();

                // Cast numeric supaya penjumlahan aman
                $totalQtyPacking = $items->sum(function ($i) {
                    return (int) ($i->qty_packing ?? 0);
                });
                $totalQtyUnit = $items->sum(function ($i) {
                    return (int) ($i->qty_unit ?? 0);
                });
                $total = $items->sum(function ($i) {
                    return (float) ($i->total ?? 0);
                });

                // Ambil product yang benar (purchaseDetail.product atau fallback ke saleDetail->product)
                $product = $first->product ?? optional($first->saleDetail)->product;
                $productName = $product->product_name ?? '-';

                // Ambil customer kalau ada (bisa null)
                $customer = optional(optional($first->saleDetail)->sale)->customer;

                return (object)[
                    'product_name' => $productName,
                    'product_id'   => $product->id ?? optional($first->saleDetail)->id_product ?? null,
                    'packing'      => $first->packing,
                    'unit'         => $first->unit,
                    'qty_packing'  => $totalQtyPacking,
                    'qty_unit'     => $totalQtyUnit,
                    'price'        => $first->price,
                    'discount'     => $first->discount ?? '0%',
                    'total'        => $total,
                    'customer'     => $customer,
                ];
            })->values(); // reset index numerik
        });

        $pdf = Pdf::loadView('Generate.purchase-grouped', [
            'purchase' => $purchase,
            'groupedDetails' => $groupedDetails
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("PO_{$purchase->order_number}.pdf");
    }


    // SO
    public function generatePdfSale($order_number)
    {
        // Ambil data sale lengkap dengan customer dan detail
        $sale = Sale::with(['customer', 'saleDetail.product'])->where('order_number', $order_number)->firstOrFail();

        // Load view PDF dan kirimkan data sale
        $pdf = Pdf::loadView('Generate.sale', compact('sale'))->setPaper('A4', 'portrait');

        // Return stream ke browser (bisa juga ->download('filename.pdf') kalau mau unduh)
        return $pdf->stream("Sale-Order-{$sale->order_number}.pdf");
    }

    // SURAT JALAN
    public function generateSJ($sj_number)
    {
        $sj = SuratJalan::with(['SJdetails.product'])->where('sj_number', $sj_number)->firstOrFail();

        $pdf = PDF::loadView('Generate.surat_jalan', compact('sj'));
        return $pdf->stream('Surat Jalan - ' . $sj->sj_number . '.pdf');
    }

    // SALE INVOICE
    public function printSaleInvoice($invoice_number)
    {
        $invoice = SaleInvoice::with([
            'suratjalan.customer',
            'suratjalan.SJdetails.product',
            'details.SJDetail.product'
        ])->where('invoice_number', $invoice_number)->firstOrFail();

        $pdf = Pdf::loadView('Generate.sale_invoice', compact('invoice'))->setPaper('A4');

        return $pdf->stream("Invoice-{$invoice_number}.pdf");
    }

    // Retur Purchase
    public function exportPdfRetur($retur_number)
    {
        $retur = ReturPurchase::with(['supplier', 'details.product'])->where('retur_number', $retur_number)->firstOrFail();

        $totalValue = $retur->details->sum('value');

        $pdf = PDF::loadView('Generate.purchase_retur', compact('retur', 'totalValue'))->setPaper('A4');
        return $pdf->stream("Retur_{$retur->retur_number}.pdf");
    }
}
