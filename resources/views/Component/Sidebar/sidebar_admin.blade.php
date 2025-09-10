@php
    $menus = [
        (object) [
            'title' => 'Dashboard',
            'path' => '/dashboard',
            'icon' => 'fas fa-home',
        ],
        (object) [
            'title' => 'Add Data',
            'icon' => 'fas fa-plus',
            'children' => array_filter([
                (object) ['title' => 'Product', 'path' => 'product-create'],
                (object) ['title' => 'Payment', 'path' => 'payment-create'],
                (object) ['title' => 'Shipping', 'path' => 'shipping-create'],
                (object) ['title' => 'Customer', 'path' => 'customer-create'],
                (object) ['title' => 'Supplier', 'path' => 'supplier-create'],
                auth()->check() && auth()->user()->main_role === 'supermanager'
                    ? (object) ['title' => 'User', 'path' => 'user-create']
                    : null,
            ]),
        ],

        (object) [
            'title' => 'List of',
            'icon' => 'fas fa-list',
            'children' => array_filter([
                (object) ['title' => 'Product', 'path' => 'product-list'],
                (object) ['title' => 'Payment', 'path' => 'payment-list'],
                (object) ['title' => 'Shipping', 'path' => 'shipping-list'],
                (object) ['title' => 'Customer', 'path' => 'customer-list'],
                (object) ['title' => 'Supplier', 'path' => 'supplier-list'],
                auth()->check() && auth()->user()->main_role === 'supermanager'
                    ? (object) ['title' => 'User', 'path' => 'users']
                    : null,
            ]),
        ],
        (object) [
            'title' => 'Purchase',
            'icon' => 'fas fa-shopping-cart',
            'children' => [
                (object) ['title' => 'Create PO', 'path' => 'purchase-create'],
                (object) ['title' => 'PO in Progress', 'path' => 'purchase-list'],
                (object) ['title' => 'List Penerimaan', 'path' => 'penerimaan-list'],
                (object) ['title' => 'List Purchase Invoice', 'path' => 'purchaseInvoice-list'],
            ],
        ],
        (object) [
            'title' => 'Sale',
            'icon' => 'fas fa-truck',
            'children' => [
                (object) ['title' => 'Create SO', 'path' => 'sale-create'],
                (object) ['title' => 'SO in Progress', 'path' => 'ordered-sales'],
                (object) ['title' => 'List Pengiriman', 'path' => 'pengiriman-list'],
                (object) ['title' => 'List Sale Invoice', 'path' => 'saleInvoice-list'],
            ],
        ],
        (object) [
            'title' => 'Retur',
            'icon' => 'fas fa-undo',
            'children' => [
                (object) ['title' => 'Create Retur Penjualan', 'path' => 'retur-sales-create'],
                (object) ['title' => 'Create Retur Pembelian', 'path' => 'retur-purchases-index'],
                (object) ['title' => 'History Retur Pembelian', 'path' => 'history-retur-purchases'],
                (object) ['title' => 'Index Retur Pembelian', 'path' => 'retur-purchase/FinalIndex'],
            ],
        ],
        (object) [
            'title' => 'Role Access',
            'icon' => 'fas fa-low-vision',
            'children' => [(object) ['title' => 'Create Access', 'path' => 'role-access']],
        ],
    ];
@endphp

<style>
    .nav-sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
        transform: scale(1.05);
        transition: all 0.3s ease;
    }
</style>

<aside class="main-sidebar sidebar-light-primary elevation-4"
    style="background: linear-gradient(145deg, #4a90e2, #50c9c3);">

    <div class="sidebar" style="padding: 10px;">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="{{ asset('templates/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2"
                    alt="User Image" style="width: 50px; border: 2px solid #fff;">
            </div>
            <div class="info">
                <span class="d-block text-white font-weight-bold">Admin</span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                @foreach ($menus as $menu)
                    @php
                        // Sembunyikan menu Role Access jika bukan supermanager
                        if ($menu->title === 'Role Access' && auth()->user()->main_role !== 'supermanager') {
                            continue;
                        }
                    @endphp

                    @if (isset($menu->children))
                        <li
                            class="nav-item has-treeview {{ collect($menu->children)->pluck('path')->contains(request()->path())? 'menu-open': '' }}">
                            <a href="#"
                                class="nav-link {{ collect($menu->children)->pluck('path')->contains(request()->path())? 'active': '' }}"
                                style="border-radius: 8px; margin-bottom: 8px;">
                                <i class="nav-icon {{ $menu->icon }}" style="color: #000000;"></i>
                                <p class="text-black" style="margin-left: 8px;">
                                    {{ $menu->title }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @foreach ($menu->children as $child)
                                    <li class="nav-item">
                                        <a href="{{ '/' . $child->path }}"
                                            class="nav-link {{ request()->path() === $child->path ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="color: #000000;"></i>
                                            <p class="text-black" style="font-size: 13px;">{{ $child->title }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ $menu->path }}"
                                class="nav-link {{ request()->path() === ltrim($menu->path, '/') ? 'active' : '' }}"
                                style="background-color: {{ request()->path() === ltrim($menu->path, '/') ? '#ffffffa8' : 'transparent' }};
                border-radius: 8px; margin-bottom: 8px;">
                                <i class="nav-icon {{ $menu->icon }}" style="color: #000000;"></i>
                                <p class="text-black" style="font-size: 14px; margin-left: 8px;">
                                    {{ $menu->title }}
                                </p>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        {{-- Optional Logout Section (still commented out) --}}
        {{-- <div style="text-align: center;">
      <form id="logout-form" method="POST" action="{{ route('admins.logout') }}">
        @csrf
        <button type="button" id="logout-btn" class="btn btn-sm btn-danger mt-4" style="border-radius: 10px; padding: 10px 20px; width: 50%;">
          <i class="fas fa-sign-out-alt"></i> Log Out
        </button>
      </form>
    </div> --}}
    </div>

    <script>
        document.getElementById('logout-btn')?.addEventListener('click', function() {
            Swal.fire({
                title: "Are you sure?",
                text: "You will be logged out from the system.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, Log Out",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        });
    </script>

</aside>
