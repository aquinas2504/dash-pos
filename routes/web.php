<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\RoleAccessController;
use App\Http\Controllers\SuratJalanController;

// =================== Login / Auth (No Middleware) ===================
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// =================== All Protected Routes ===================
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', fn() => view('welcome'));

    //Draft
    Route::post('/drafts/save', [DraftController::class, 'save'])->name('drafts.save');
    Route::get('/drafts', [DraftController::class, 'index'])->name('drafts.index');
    Route::delete('/drafts/{id}', [DraftController::class, 'delete'])->name('drafts.delete');

    // Hak Akses Role (supermanager only)
    Route::middleware('role:supermanager')->group(function () {

        // CRUD Role Access
        Route::get('/role-access', [RoleAccessController::class, 'index'])->name('role.access');
        Route::post('/role-access/update', [RoleAccessController::class, 'update'])->name('role.access.update');

        // CRUD User
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/user-create', [UserController::class, 'create'])->name('users.create');
        Route::post('/user-store', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{id}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');

        // CRUD Payment
        Route::get('/payment-list', [PaymentController::class, 'index'])->name('payment.index');
        Route::get('/payment-create', [PaymentController::class, 'create'])->name('payment.create');
        Route::post('/payments/store', [PaymentController::class, 'store'])->name('payment.store');
        Route::get('/payments/edit/{id}', [PaymentController::class, 'edit'])->name('payment.edit');
        Route::put('/payments/update/{id}', [PaymentController::class, 'update'])->name('payment.update');
    });

    // Product
    Route::get('/product-list', [ProductController::class, 'index'])->name('products.index')->middleware('checkpermission:products.index');
    Route::get('/product-create', [ProductController::class, 'create'])->name('products.create')->middleware('checkpermission:products.create');
    Route::post('/product-store', [ProductController::class, 'store'])->name('products.store')->middleware('checkpermission:products.store');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('checkpermission:products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update')->middleware('checkpermission:products.update');

    // Shipping
    Route::get('/shipping-list', [ShippingController::class, 'index'])->name('shippings.index')->middleware('checkpermission:shippings.index');
    Route::get('/shipping-create', [ShippingController::class, 'create'])->name('shippings.create')->middleware('checkpermission:shippings.create');
    Route::post('/shipping-store', [ShippingController::class, 'store'])->name('shippings.store')->middleware('checkpermission:shippings.store');
    Route::get('/shipping/{id}/edit', [ShippingController::class, 'edit'])->name('shippings.edit')->middleware('checkpermission:shippings.edit');
    Route::put('/shipping/{shipping}', [ShippingController::class, 'update'])->name('shippings.update')->middleware('checkpermission:shippings.update');

    // Customer
    Route::get('/customer-list', [CustomerController::class, 'index'])->name('customers.index')->middleware('checkpermission:customers.index');
    Route::get('/customer-create', [CustomerController::class, 'create'])->name('customers.create')->middleware('checkpermission:customers.create');
    Route::post('/customer-store', [CustomerController::class, 'store'])->name('customers.store')->middleware('checkpermission:customers.store');
    Route::get('/customers/{customer_code}/edit', [CustomerController::class, 'edit'])->name('customers.edit')->middleware('checkpermission:customers.edit');
    Route::put('/customers/{customer_code}', [CustomerController::class, 'update'])->name('customers.update')->middleware('checkpermission:customers.update');

    // Supplier
    Route::get('/supplier-list', [SupplierController::class, 'index'])->name('suppliers.index')->middleware('checkpermission:suppliers.index');
    Route::get('/supplier-create', [SupplierController::class, 'create'])->name('suppliers.create')->middleware('checkpermission:suppliers.create');
    Route::post('/supplier-store', [SupplierController::class, 'store'])->name('suppliers.store')->middleware('checkpermission:suppliers.store');
    Route::get('/suppliers/{supplier_code}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit')->middleware('checkpermission:suppliers.edit');
    Route::put('/suppliers/{supplier_code}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware('checkpermission:suppliers.update');

    // Purchase
    Route::get('/purchase-create', [PurchaseController::class, 'create'])->name('purchases.create')->middleware('checkpermission:purchases.create');
    Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchases.store')->middleware('checkpermission:purchases.store');
    Route::get('/purchase-list', [PurchaseController::class, 'index'])->name('purchases.index')->middleware('checkpermission:purchases.index');

    // Sale
    Route::get('/sale-create', [SaleController::class, 'create'])->name('sales.create')->middleware('checkpermission:sales.create');
    Route::post('/sale', [SaleController::class, 'store'])->name('sales.store')->middleware('checkpermission:sales.store');
    Route::get('/ordered-sales', [SaleController::class, 'orderedSales'])->name('sales.ordered')->middleware('checkpermission:sales.ordered');

    // Surat Jalan
    Route::get('/sale-details/{order_number}', [SaleController::class, 'showSaleDetails'])->name('sales.details')->middleware('checkpermission:sales.details');
    Route::post('/create-surat-jalan', [SuratJalanController::class, 'storeSuratJalan'])->name('SJ.Store')->middleware('checkpermission:SJ.Store');
    Route::get('/surat-jalan/manual', [SuratJalanController::class, 'createManual'])->name('SJ.CreateManual')->middleware('checkpermission:SJ.CreateManual');
    Route::post('/surat-jalan/manual', [SuratJalanController::class, 'storeManual'])->name('SJ.StoreManual')->middleware('checkpermission:SJ.StoreManual');
    Route::get('/pengiriman-list', [SuratJalanController::class, 'index'])->name('pengirimans.index')->middleware('checkpermission:pengirimans.index');

    // Penerimaan Barang
    Route::get('/penerimaan/from-po/{orderNumber}', [PenerimaanController::class, 'createFromPO'])->where('orderNumber', '.*')->name('penerimaan.create.fromPO')->middleware('checkpermission:penerimaan.create.fromPO');
    Route::post('/penerimaan/store', [PenerimaanController::class, 'store'])->name('penerimaan.store')->middleware('checkpermission:penerimaan.store');
    Route::get('/penerimaan/manual', [PenerimaanController::class, 'createManual'])->name('penerimaan.create.manual')->middleware('checkpermission:penerimaan.create.manual');
    Route::post('/penerimaans/manual/store', [PenerimaanController::class, 'storeManual'])->name('penerimaans.manual.store')->middleware('checkpermission:penerimaans.manual.store');
    Route::get('/penerimaan-list', [PenerimaanController::class, 'index'])->name('penerimaans.index')->middleware('checkpermission:penerimaans.index');

    // Invoice Pembelian
    Route::get('/invoice/create/{penerimaan_number}', [InvoiceController::class, 'create'])->where('penerimaan_number', '.*')->name('invoice.create')->middleware('checkpermission:invoice.create');
    Route::post('/invoices/store', [InvoiceController::class, 'store'])->name('invoices.store')->middleware('checkpermission:invoices.store');
    Route::get('/purchaseInvoice-list', [InvoiceController::class, 'indexPurchaseInvoice'])->name('purchaseInvoice.index')->middleware('checkpermission:purchaseInvoice.index');
    Route::get('/invoices/purchase/{id}/edit', [InvoiceController::class, 'editPurchaseInvoice'])->name('invoices.purchase.edit')->middleware('checkpermission:invoices.purchase.edit');
    Route::put('/invoices/purchase/{id}', [InvoiceController::class, 'updatePurchaseInvoice'])->name('invoices.purchase.update')->middleware('checkpermission:invoices.purchase.update');

    // Invoice Penjualan
    Route::get('/sale-invoice/create/{sj_number}', [InvoiceController::class, 'createSJ'])->where('sj_number', '.*')->name('invoice.createSJ')->middleware('checkpermission:invoice.createSJ');
    Route::post('/sale-invoice/store', [InvoiceController::class, 'storeSJ'])->name('invoiceSJ.store')->middleware('checkpermission:invoiceSJ.store');
    Route::get('/saleInvoice-list', [InvoiceController::class, 'indexSaleInvoice'])->name('saleInvoice.index')->middleware('checkpermission:saleInvoice.index');
    Route::get('/invoice/{invoice}/edit', [InvoiceController::class, 'editSaleInvoice'])->name('invoiceSJ.edit')->middleware('checkpermission:invoiceSJ.edit');
    Route::put('/invoice/{invoice}', [InvoiceController::class, 'updateSaleInvoice'])->name('invoiceSJ.update')->middleware('checkpermission:invoiceSJ.update');

    // Generate
    Route::get('/purchase/{order_number}/pdf', [GenerateController::class, 'generatePdf'])->where('order_number', '.*')->name('purchase.pdf')->middleware('checkpermission:purchase.pdf');
    Route::get('/purchase-grouped/{order_number}/pdf', [GenerateController::class, 'generatePdf2'])->where('order_number', '.*')->name('purchase-grouped.pdf')->middleware('checkpermission:purchase-grouped.pdf');
    Route::get('/sale/{order_number}/pdf', [GenerateController::class, 'generatePdfSale'])->name('sale.pdf')->middleware('checkpermission:sale.pdf');
    Route::get('/SJ/{sj_number}/pdf', [GenerateController::class, 'generateSJ'])->where('sj_number', '.*')->name('SJ.Print')->middleware('checkpermission:SJ.Print');
    Route::get('/Sale-Invoice/{invoice_number}/pdf', [GenerateController::class, 'printSaleInvoice'])->name('saleInvoice.Print')->middleware('checkpermission:saleInvoice.Print');

    // API & Search (dibiarkan tanpa checkpermission)
    Route::get('/products-search', [ProductController::class, 'search']);
    Route::get('/search-products', [SaleController::class, 'search']);
    Route::get('/suppliers-search', [SupplierController::class, 'searchSuppliers']);
    Route::get('/customers-search', [CustomerController::class, 'searchCustomers']);
    Route::get('/shippings-search', [ShippingController::class, 'searchShippings']);
    Route::get('/product-packings/{productId}', [ProductController::class, 'getPackingOptions']);
    Route::get('/product-packings/{product}', [SaleController::class, 'getPackings']);
    Route::get('/api/getPendingPurchases', [SuratJalanController::class, 'getPendingPurchases']);
    Route::get('/pending-sales', [SaleController::class, 'getPendingSales']);


    // BARU Retur
    // Form & Simpan Retur
    Route::get('/retur-sales-create', [ReturController::class, 'createSaleRetur'])->name('retur-sales.create');
    Route::post('/retur-sales', [ReturController::class, 'storeSaleRetur'])->name('retur-sales.store');

    // API untuk pencarian dan detail invoice (digunakan di form Blade)
    Route::get('/invoice-search', [ReturController::class, 'searchSaleRetur']);
    Route::get('/invoice-detail/{invoiceNumber}', [ReturController::class, 'getDetailSaleRetur']);

    // API untuk history retur
    Route::get('/retur-history/{invoice}', [ReturController::class, 'getReturHistory']);

    // Fitur search invoice number berdasarkan customer dan product
    Route::get('/search-customer', [ReturController::class, 'customer']);
    Route::get('/search-product', [ReturController::class, 'product']);
    Route::get('/search-invoices', [ReturController::class, 'invoiceByProductAndCustomer']);


    // Retur Pembelian bagian save list sementara 
    Route::get('/retur-purchase/search-invoice', [ReturController::class, 'searchInvoice'])->name('retur-purchase.searchInvoice');
    Route::post('/retur-purchase/store', [ReturController::class, 'store'])->name('retur-purchase.store');

    // Retur Pembelian Main Index
    Route::get('/retur-purchases-index', [ReturController::class, 'index'])->name('retur-purchases.index');

    // Index History Purchase Retur
    Route::get('/history-retur-purchases', [ReturController::class, 'indexHistoryReturPurchase'])->name('history-retur-purchases.index');

    // Store, Index, dan Generate PDF Purchase Retur
    Route::get('/retur-purchase/FinalIndex', [ReturController::class, 'indexPurchaseRetur'])->name('retur-purchase.FinalIndex');
    Route::get('/pdf/{retur_number}', [GenerateController::class, 'exportPdfRetur'])->name('retur-purchase.pdf');
    Route::post('/retur-purchase/FinalStore', [ReturController::class, 'storePurchaseRetur'])->name('retur-purchase.FinalStore');
});
