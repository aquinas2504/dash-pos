@extends('Component.main_admin')

@section('content')
    <div class="container">
        <h2 class="mb-4">Manajemen Role Access</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @foreach ($roles as $role)
            <form method="POST" action="{{ route('role.access.update') }}" class="mb-5 p-4 border rounded shadow-sm bg-light">
                @csrf
                <h4 class="mb-3">Role: <strong>{{ ucfirst($role) }}</strong></h4>
                <input type="hidden" name="role" value="{{ $role }}">

                @php
                    $currentPermissions = $rolePermissions[$role] ?? collect();
                    $grouped = $permissions->groupBy('group');
                @endphp

                @foreach ($grouped as $groupName => $groupPerms)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ $groupName }}</strong>
                            <label style="font-size: 0.9rem;">
                                <input type="checkbox" class="check-group" data-group="{{ Str::slug($groupName) }}">
                                <em>Check semua</em>
                            </label>
                        </div>
                        <div class="row">
                            @foreach ($groupPerms as $perm)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input checkbox-{{ Str::slug($groupName) }}" type="checkbox"
                                            name="permissions[]" value="{{ $perm->id }}"
                                            {{ $currentPermissions->pluck('permission_id')->contains($perm->id) ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            {{ $perm->label ?? $perm->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary mt-2">Simpan Akses</button>
            </form>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('form').forEach(function(form) {
            form.querySelectorAll('.check-group').forEach(function(groupCheckbox) {
                groupCheckbox.addEventListener('change', function() {
                    const groupClass = 'checkbox-' + this.dataset.group;
                    const checkboxes = form.querySelectorAll('.' + groupClass);
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            });
        });
    </script>
@endsection
