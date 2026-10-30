@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h4 class="mb-0">Stores (المتاجر)</h4>
            <a href="{{ route('admin.stores.create') }}" class="btn btn-primary btn-sm">Add New Store</a>
        </div>
        
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stores as $store)
                        <tr>
                            <td>{{ $store->id }}</td>
                            <td class="fw-bold">{{ $store->name }}</td>
                            <td>{{ $store->domain ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $store->is_active ? 'success' : 'danger' }}">
                                    {{ $store->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.stores.edit', $store->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.stores.destroy', $store->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this store?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $stores->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection