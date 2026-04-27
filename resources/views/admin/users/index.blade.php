@extends('layouts.admin')
@section('title', 'Kelola User')
@section('page-title', 'Kelola User')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Kelola User</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Daftar User</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah User
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama/NIP/email/WA..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-control">
                        <option value="">Semua Role</option>
                        <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="atasan" {{ request('role') == 'atasan' ? 'selected' : '' }}>Atasan</option>
                        <option value="pegawai" {{ request('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Cari</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama / NIP</th>
                        <th>WhatsApp</th>
                        <th>Role</th>
                        <th>Jabatan</th>
                        <th>Atasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $i }}</td>
                        <td>
                            <strong>{{ $user->name }}</strong><br>
                            <small style="color: var(--text-muted);">{{ $user->nip }}</small>
                        </td>
                        <td>{{ $user->phone ?: '-' }}</td>
                        <td>
                            @if($user->role == 'super_admin')
                                <span class="badge badge-danger">Super Admin</span>
                            @elseif($user->role == 'atasan')
                                <span class="badge badge-info">Atasan</span>
                            @else
                                <span class="badge badge-success">Pegawai</span>
                            @endif
                        </td>
                        <td>{{ $user->jabatan ?? '-' }}</td>
                        <td>{{ $user->atasan->name ?? '-' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center" style="color: var(--text-muted);">Tidak ada data user</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $users->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
