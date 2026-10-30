@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header bg-white">
            <h4 class="mb-0">Add New Store</h4>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.stores.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Store Name (e.g., Amazon, Bol)</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Domain (e.g., amazon.nl)</label>
                    <input type="text" name="domain" class="form-control" value="{{ old('domain') }}">
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                    <label class="form-check-label fw-bold" for="is_active">Active</label>
                </div>
                <button type="submit" class="btn btn-primary">Save Store</button>
            </form>
        </div>
    </div>
</div>
@endsection