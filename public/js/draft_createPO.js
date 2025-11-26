document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('purchase-form');

    if (!form) return;

    // Ambil semua data form
    function collectFormData() {
        const data = {};

        // Header PO
        data.order_number = document.getElementById('order_number')?.value || '';
        data.order_date = document.getElementById('order_date')?.value || '';
        data.supplier_code = document.getElementById('supplier_code')?.value || '';
        data.supplier_name = document.getElementById('search-supplier')?.value || '';
        data.note = document.querySelector('[name="note"]')?.value || '';
        data.po_mode = document.getElementById('po_mode')?.value || '';

        // Ambil status PPN tergantung mode
        data.ppn_status_manual = '';
        data.ppn_status_so = '';

        if (data.po_mode === 'manual') {
            data.ppn_status_manual = document.querySelector('input[name="ppn_option_manual"]:checked')?.value || '';
        } else if (data.po_mode === 'so') {
            data.ppn_status_so = document.getElementById('ppn_status_so')?.value || '';
        }

        


        // Produk
        data.products = [];
        document.querySelectorAll('#product-table tbody tr').forEach(tr => {
            // default values
            let so_detail = '';
            let id_product = '';
            let name = '';
            let code = '';
            let qty_packing = '';
            let packing = '';
            let qty_unit = '';
            let unit = '';
            let price = '';
            let discount = '';
            let total = '';

            if (data.po_mode === 'manual') {
                // mode manual
                id_product = tr.querySelector('input[name="id_product[]"]')?.value || '';
                name = tr.children[0]?.textContent?.trim() || '';
                code = tr.children[1]?.textContent?.trim() || '';
                qty_packing = tr.querySelector('.input-packing-qty')?.value || '';
                packing = tr.querySelector('.select-packing')?.value || '';
                qty_unit = tr.querySelector('.qty-unit')?.value || '';
                unit = tr.querySelector('.select-unit')?.value || '';
                price = tr.querySelector('.price-input')?.value || '';
                discount = tr.querySelector('.discount-input')?.value || '';
                total = tr.querySelector('.total-cell')?.textContent?.trim() || '';
            } else if (data.po_mode === 'so') {
                // mode SO
                so_detail = tr.querySelector('input[name="id_sale_detail[]"]')?.value || '';
                name = tr.children[1]?.textContent?.trim() || ''; // kolom 2 = nama (sesuaikan)
                code = tr.children[2]?.textContent?.trim() || ''; // kolom 3 = code
                price = tr.querySelector('input[name="price[]"]')?.value || '';
                discount = tr.querySelector('input[name="discount[]"]')?.value || '';
                total = tr.querySelector('input[name="total[]"]')?.value || '';
            }

            data.products.push({
                id_product,
                so_detail,
                name,
                code,
                qty_packing,
                packing,
                qty_unit,
                unit,
                price,
                discount,
                total
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
                form_type: 'purchase_order',
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
