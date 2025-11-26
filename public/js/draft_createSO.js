document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('purchase-form');

    if (!form) return;

    // Ambil semua data form
    function collectFormData() {
        const data = {};

        // Header PO
        data.order_number = document.getElementById('order_number')?.value || '';
        data.order_date = document.getElementById('date')?.value || '';
        data.supplier_code = document.getElementById('customer_code')?.value || '';
        data.supplier_name = document.getElementById('search-customer')?.value || '';
        data.note = document.getElementById('note')?.value || '';
        data.ppn_status = document.querySelector('input[name="ppn"]:checked')?.value || 'yes';

        // Ambil code
        data.ship_1 = document.querySelector('select[name="ship_1"]')?.value || '';
        data.ship_2 = document.querySelector('select[name="ship_2"]')?.value || '';

        // Ambil nama yang tampil di UI
        data.ship_1_name = document.querySelector('select[name="ship_1"] option:checked')?.textContent.trim() || '';
        data.ship_2_name = document.querySelector('select[name="ship_2"] option:checked')?.textContent.trim() || '';

        data.top    = document.getElementById('top')?.value || '';

        
        // Produk
        data.products = [];
        document.querySelectorAll('#product-table tbody tr').forEach(tr => {
            data.products.push({
                id_product: tr.dataset.productId || '',
                code: tr.querySelector('.cell-code')?.textContent.trim() || '',
                name: tr.querySelector('.cell-name')?.textContent.trim() || '',
                packing: tr.dataset.packingId || '',
                packing_name: tr.querySelector('.cell-packing')?.textContent.replace(/^\s*\d*\s*x?\s*/, '') || '',
                qty_packing: tr.dataset.qtyPacking || '',
                unit: tr.dataset.unitId || '',
                unit_name: tr.querySelector('.cell-qtyunit')?.textContent.replace(/^\s*\d*\s*/, '') || '',
                qty_unit: tr.dataset.qtyUnit || '',
                price: tr.dataset.price || '',
                discount: tr.dataset.discount || '',
                total: tr.dataset.total || ''
            });
        });


        // Total
        data.subtotal = document.getElementById('subtotal')?.value || '0';
        data.ppn = document.getElementById('ppn')?.value || '0';
        data.grand_total = document.getElementById('grand_total')?.value || '0';


        return data;
    }

    // Autosave draft
    function autoSaveDraft() {
        const formData = collectFormData();

        fetch(draftSaveConfig.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': draftSaveConfig.csrf
            },
            body: JSON.stringify({
                form_type: 'sale_order',
                form_id: null, // bisa diganti kalau edit mode
                url: window.location.pathname,
                data: formData
            })
        })
            .then(res => res.json())
            .then(data => {
                console.log('✅ Draft autosaved:', data);
            })
            .catch(err => console.error('❌ Draft save failed:', err));
    }

    // Jalankan autosave setiap 5 detik
    setInterval(autoSaveDraft, 5000);
});
