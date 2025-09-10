<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\Packing;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Shipping;
use App\Models\Supplier;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * ================== USERS ==================
         */

        // catatan untuk user main_role ada supermanager dan umum, untuk sub_role ada admin dan sales
        $users = [
            [
                'name'       => 'Super Manager',
                'email'      => 'superadmin@dash.com',
                'password'   => Hash::make('dash.admin123'),
                'main_role'  => 'supermanager',
                'sub_role'   => null,
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
        }

        /**
         * ================== PACKING & UNIT ==================
         */
        $packings = ['Ikat', 'Dus', 'Bal'];
        foreach ($packings as $packing) {
            Packing::create(['packing_name' => $packing]);
        }

        $units = [
            ['unit_code' => 'pcs',   'unit_name' => 'Pieces'],
            ['unit_code' => 'set',   'unit_name' => 'Set'],
            ['unit_code' => 'lusin', 'unit_name' => 'Lusin'],
            ['unit_code' => 'gross', 'unit_name' => 'Gross'],
        ];
        foreach ($units as $unit) {
            Unit::create($unit);
        }

        /**
         * ================== PERMISSIONS ==================
         */
        $permissions = [
            // Produk
            ['name' => 'products.index',   'label' => 'Lihat Daftar Produk', 'group' => 'Produk'],
            ['name' => 'products.create',  'label' => 'Tambah Produk',       'group' => 'Produk'],
            ['name' => 'products.store',   'label' => 'Simpan Produk Baru',  'group' => 'Produk'],
            ['name' => 'products.edit',    'label' => 'Edit Produk',         'group' => 'Produk'],
            ['name' => 'products.update',  'label' => 'Update Produk',       'group' => 'Produk'],
            
            // Shipping
            ['name' => 'shippings.index',   'label' => 'Lihat Daftar Shipping', 'group' => 'Shipping'],
            ['name' => 'shippings.create',  'label' => 'Tambah Shipping',       'group' => 'Shipping'],
            ['name' => 'shippings.store',   'label' => 'Simpan Shipping Baru',  'group' => 'Shipping'],
            ['name' => 'shippings.edit',    'label' => 'Edit Shipping',         'group' => 'Shipping'],
            ['name' => 'shippings.update',  'label' => 'Update Shipping',       'group' => 'Shipping'],

            // Customer
            ['name' => 'customers.index',  'label' => 'Lihat Daftar Customer', 'group' => 'Customer'],
            ['name' => 'customers.create', 'label' => 'Tambah Customer',       'group' => 'Customer'],
            ['name' => 'customers.store',  'label' => 'Simpan Customer',       'group' => 'Customer'],
            ['name' => 'customers.edit',   'label' => 'Edit Customer',         'group' => 'Customer'],
            ['name' => 'customers.update', 'label' => 'Update Customer',       'group' => 'Customer'],

            // Supplier
            ['name' => 'suppliers.index',  'label' => 'Lihat Daftar Supplier', 'group' => 'Supplier'],
            ['name' => 'suppliers.create', 'label' => 'Tambah Supplier',       'group' => 'Supplier'],
            ['name' => 'suppliers.store',  'label' => 'Simpan Supplier',       'group' => 'Supplier'],
            ['name' => 'suppliers.edit',   'label' => 'Edit Supplier',         'group' => 'Supplier'],
            ['name' => 'suppliers.update', 'label' => 'Update Supplier',       'group' => 'Supplier'],

            // Purchase
            ['name' => 'purchases.index',  'label' => 'Lihat Daftar Purchase', 'group' => 'Purchase'],
            ['name' => 'purchases.create', 'label' => 'Tambah Purchase',       'group' => 'Purchase'],
            ['name' => 'purchases.store',  'label' => 'Simpan Purchase',       'group' => 'Purchase'],

            // Sales
            ['name' => 'sales.create',  'label' => 'Buat Penjualan',                   'group' => 'Sales'],
            ['name' => 'sales.store',   'label' => 'Simpan Penjualan',                 'group' => 'Sales'],
            ['name' => 'sales.ordered', 'label' => 'Lihat Penjualan yang Sudah Dipesan','group' => 'Sales'],
            ['name' => 'sales.details', 'label' => 'Lihat Detail Penjualan (SO)',      'group' => 'Sales'],

            // Surat Jalan
            ['name' => 'SJ.Store',        'label' => 'Simpan Surat Jalan dari SO', 'group' => 'Surat Jalan'],
            ['name' => 'SJ.CreateManual', 'label' => 'Buat Surat Jalan Manual',    'group' => 'Surat Jalan'],
            ['name' => 'SJ.StoreManual',  'label' => 'Simpan Surat Jalan Manual',  'group' => 'Surat Jalan'],
            ['name' => 'pengirimans.index','label' => 'Lihat Daftar Pengiriman',   'group' => 'Surat Jalan'],

            // Penerimaan
            ['name' => 'penerimaans.index',          'label' => 'Lihat Daftar Penerimaan', 'group' => 'Penerimaan'],
            ['name' => 'penerimaan.create.fromPO',   'label' => 'Form Penerimaan dari PO', 'group' => 'Penerimaan'],
            ['name' => 'penerimaan.create.manual',   'label' => 'Form Penerimaan Manual',  'group' => 'Penerimaan'],
            ['name' => 'penerimaan.store',           'label' => 'Simpan Penerimaan dari PO','group' => 'Penerimaan'],
            ['name' => 'penerimaans.manual.store',   'label' => 'Simpan Penerimaan Manual', 'group' => 'Penerimaan'],

            // Invoice Pembelian
            ['name' => 'invoice.create',           'label' => 'Buat Invoice Pembelian',   'group' => 'Invoice Pembelian'],
            ['name' => 'invoices.store',           'label' => 'Simpan Invoice Pembelian', 'group' => 'Invoice Pembelian'],
            ['name' => 'purchaseInvoice.index',    'label' => 'Lihat Invoice Pembelian',  'group' => 'Invoice Pembelian'],
            ['name' => 'invoices.purchase.edit',   'label' => 'Edit Invoice Pembelian',   'group' => 'Invoice Pembelian'],
            ['name' => 'invoices.purchase.update', 'label' => 'Update Invoice Pembelian', 'group' => 'Invoice Pembelian'],

            // Invoice Penjualan
            ['name' => 'invoice.createSJ',   'label' => 'Buat Invoice Penjualan',    'group' => 'Invoice Penjualan'],
            ['name' => 'invoiceSJ.store',    'label' => 'Simpan Invoice Penjualan',  'group' => 'Invoice Penjualan'],
            ['name' => 'saleInvoice.index',  'label' => 'Lihat Invoice Penjualan',   'group' => 'Invoice Penjualan'],
            ['name' => 'invoiceSJ.edit',     'label' => 'Edit Invoice Penjualan',    'group' => 'Invoice Penjualan'],
            ['name' => 'invoiceSJ.update',   'label' => 'Update Invoice Penjualan',  'group' => 'Invoice Penjualan'],

            // Cetak/PDF
            ['name' => 'purchase.pdf',        'label' => 'Download PO (PDF)',      'group' => 'Cetak/PDF'],
            ['name' => 'purchase-grouped.pdf','label' => 'Download PO Grouped (PDF)','group' => 'Cetak/PDF'],
            ['name' => 'sale.pdf',            'label' => 'Download SO (PDF)',      'group' => 'Cetak/PDF'],
            ['name' => 'SJ.Print',            'label' => 'Cetak Surat Jalan',      'group' => 'Cetak/PDF'],
            ['name' => 'saleInvoice.Print',   'label' => 'Cetak Invoice Penjualan','group' => 'Cetak/PDF'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                ['label' => $perm['label'], 'group' => $perm['group']]
            );
        }

        /**
         * ================== SHIPPING ==================
         */
        // $shippings = [
        //     ['shipping_code' => 'shipping-001', 'shipping_name' => 'Garuda', 'address' => 'Alamat A'],
        //     ['shipping_code' => 'shipping-002', 'shipping_name' => 'Badak',  'address' => 'Alamat A'],
        //     ['shipping_code' => 'shipping-003', 'shipping_name' => 'Hiu',    'address' => 'Alamat A'],
        // ];
        // foreach ($shippings as $shipping) {
        //     Shipping::create($shipping);
        // }

        /**
         * ================== CUSTOMERS ==================
         */
        // $customers = [
        //     ['customer_code' => 'customer-001', 'customer_name' => 'PT ABC', 'customer_email' => 'ABC@gmail.com'],
        //     ['customer_code' => 'customer-002', 'customer_name' => 'PT DEF', 'customer_email' => 'DEF@gmail.com'],
        //     ['customer_code' => 'customer-003', 'customer_name' => 'PT GHI', 'customer_email' => 'GHI@gmail.com'],
        // ];
        // foreach ($customers as $cust) {
        //     Customer::create(array_merge($cust, [
        //         'customer_phone' => '085716862515',
        //         'npwp'           => '123456',
        //         'city'           => 'Jakarta',
        //         'country'        => 'Indonesia',
        //         'address'        => 'Jl Hj Fatimah no 157 A Duri Kosambi',
        //     ]));
        // }

        /**
         * ================== SUPPLIERS ==================
         */
        // $suppliers = [
        //     ['supplier_code' => 'supplier-001', 'supplier_name' => 'PT JKL', 'supplier_email' => 'JKL@gmail.com'],
        //     ['supplier_code' => 'supplier-002', 'supplier_name' => 'PT MNO', 'supplier_email' => 'MNO@gmail.com'],
        //     ['supplier_code' => 'supplier-003', 'supplier_name' => 'PT PQR', 'supplier_email' => 'PQR@gmail.com'],
        // ];
        // foreach ($suppliers as $supp) {
        //     Supplier::create(array_merge($supp, [
        //         'supplier_phone' => '085716862515',
        //         'npwp'           => '123456',
        //         'city'           => 'Jakarta',
        //         'country'        => 'Indonesia',
        //         'address'        => 'Jl Hj Fatimah no 157 A Duri Kosambi',
        //     ]));
        // }

        /**
         * ================== PAYMENTS ==================
         */
        // $payments = [
        //     ['rekening_name' => 'PT. Dash Megah Internasional', 'rekening_number' => '1234567890', 'bank_name' => 'BCA'],
        //     ['rekening_name' => 'PT. Dash Megah Internasional', 'rekening_number' => '9876543210', 'bank_name' => 'Mandiri'],
        //     ['rekening_name' => 'PT. Dash Megah Internasional', 'rekening_number' => '1122334455', 'bank_name' => 'BRI'],
        // ];
        // foreach ($payments as $payment) {
        //     Payment::create($payment);
        // }
    }
}
