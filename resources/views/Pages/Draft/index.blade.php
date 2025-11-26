@extends('Component.main_admin')

@section('content')
    <div class="container">
        <h3>Daftar Draft Kamu</h3>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Form Type</th>
                    <th>Terakhir Diubah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drafts as $draft)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $draft->form_type)) }}</td>
                        <td>{{ $draft->updated_at->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ $draft->url }}" class="btn btn-sm btn-success">Lanjutkan</a>
                            <form action="{{ route('drafts.delete', $draft->id) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada draft</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
