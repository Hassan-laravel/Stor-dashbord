@extends('layouts.admin')

@section('content')
    <div class="container-fluid mt-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- بداية شريط الفلترة الاحترافي -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.products.index') }}" method="GET">
                    <div class="row align-items-end">

                        <!-- حقل البحث بالاسم -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="text-muted mb-1"><i class="fas fa-search"></i> Search by Name</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="Enter product title...">
                        </div>

                        <!-- قائمة المخازن -->
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="text-muted mb-1"><i class="fas fa-store"></i> Store</label>
                            <select name="store_id" class="form-select form-control">
                                <option value="">All Stores</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}"
                                        {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- قائمة التصنيفات -->
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="text-muted mb-1"><i class="fas fa-tags"></i> Category</label>
                            <select name="category_id" class="form-select form-control">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- أزرار التحكم -->
                        <div class="col-md-2 d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-dark w-100">
                                Filter
                            </button>
                            @if (request()->hasAny(['search', 'store_id', 'category_id']))
                                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-danger w-100"
                                    title="Clear Filters">
                                    Clear
                                </a>
                            @endif
                        </div>

                    </div>
                </form>
            </div>
        </div>
        <!-- نهاية شريط الفلترة -->
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h4 class="mb-0">Products</h4>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Product Name (EN)</th>
                                <th>Code (ASIN/EAN)</th>
                                <th>Store</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>

                                    {{-- جلب العنوان باللغة الإنجليزية كعرض افتراضي --}}
                                    <td class="fw-bold">
                                        {{ Str::limit($product->translate('en')->title ?? 'N/A', 50) }}
                                    </td>

                                    <td><span class="badge bg-secondary">{{ $product->product_code }}</span></td>

                                    <td>{{ $product->store->name ?? 'Unknown' }}</td>

                                    <td>{{ $product->category->translate('en')->name ?? 'Unknown' }}</td>

                                    <td>
                                        @if ($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success" style="font-size: 1.1rem;">
                                            €{{ $product->price }}
                                        </div>

                                        @if ($product->original_price && $product->original_price > $product->price)
                                            <div class="text-muted"
                                                style="text-decoration: line-through; font-size: 0.9rem;">
                                                €{{ $product->original_price }}
                                            </div>
                                        @endif

                                        @if ($product->discount_percentage > 0)
                                            <span class="badge bg-danger mt-1" style="font-size: 0.85rem;">
                                                {{ $product->discount_percentage }}% OFF
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No products found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ترقيم الصفحات --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
