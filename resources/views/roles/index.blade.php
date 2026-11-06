@extends('layouts.app')

@section('title', 'Peran')

@section('content')
<div class="page-head">
    <div>
        <h1>Peran pengguna</h1>
        <p>Tiga peran bersifat tetap karena menentukan hak akses. Nama tampilannya bisa diubah.</p>
    </div>
</div>

@if($errors->any())
    <div class="notice notice-err">@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>
@endif

<div class="panel">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>#</th><th>Nama peran</th><th>Jumlah pengguna</th><th></th></tr></thead>
            <tbody>
            @foreach($roles as $r)
                <tr>
                    <td class="num">{{ $r->id }}</td>
                    <td colspan="3" class="p-0">
                        <form method="POST" action="{{ route('roles.update', $r) }}" class="d-flex align-items-center gap-3 px-3 py-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $r->name }}" class="form-control form-control-sm" style="max-width: 260px" required>
                            <span class="text-secondary flex-grow-1">{{ $r->users_count }} pengguna</span>
                            <button class="btn btn-outline-primary btn-sm">Simpan</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
