@extends('layouts.admin')
@section('title', 'Bol.com Products Management')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-boxes text-primary"></i> Bol.com Products Management</h2>
        <a href="{{ route('admin.bol-products.create') }}" class="btn btn-primary fw-bold">
            <i class="fas fa-plus me-1"></i> Add New Product (EAN)
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Products Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Product Title</th>
                            <th>Product Code (EAN)</th>
                            <th>Price</th>
                            <th>Rating</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <img src="{{ $product->image_url }}" alt="" class="rounded bg-light p-1" style="width: 50px; height: 50px; object-fit: contain;">
                                </td>
                                <td class="fw-semibold">
                                    {{ $product->translate(app()->getLocale())->title ?? $product->translate('nl')->title ?? 'No Title' }}
                                </td>
                                <td><code>{{ $product->product_code }}</code></td>
                                <td class="fw-bold text-success">€{{ number_format($product->price, 2) }}</td>
                                <td>
                                    <span class="text-warning"><i class="fas fa-star"></i></span> {{ $product->rating ?? '4.5' }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.bol-products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.bol-products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No Bol.com products added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection