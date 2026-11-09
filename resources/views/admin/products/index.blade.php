@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">

    <!-- شريط الفلترة العلوي -->
    <div class="card shadow-sm mb-4 bg-white border-0">
        <div class="card-body">
            <form action="{{ route('admin.products.index') }}" method="GET" class="row g-3 align-items-center">
                
                <!-- البحث بالاسم -->
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold"><i class="fas fa-search"></i> Search by Name</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Enter product title...">
                </div>

                <!-- تصفية حسب المخزن -->
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold"><i class="fas fa-store"></i> Store</label>
                    <select name="store_id" class="form-select">
                        <option value="">All Stores</option>
                        @foreach($stores ?? [] as $store)
                            <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- تصفية حسب التصنيف -->
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold"><i class="fas fa-tags"></i> Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->translate('en')->name ?? 'Category' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- أزرار التصفية -->
                <div class="col-md-2 d-flex gap-2 align-items-end pt-3">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">Filter</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>

            </form>
        </div>
    </div>

    <!-- رأس الصفحة وأزرار الإضافة الجماعية -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold">Products Management</h4>
        <div>
            <a href="{{ route('admin.products.bulk-import') }}" class="btn btn-success me-2">
                <i class="fas fa-cloud-download-alt"></i> Bulk Import
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- نموذج الحذف الجماعي -->
    <form action="{{ route('admin.products.bulk-delete') }}" method="POST">
        @csrf
        @method('DELETE')

        <!-- شريط أدوات الحذف الجماعي المشروط -->
        <div class="card shadow-sm mb-3 border-danger bg-light">
            <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
                
                <!-- زر حذف المنتجات المحددة يدوياً -->
                <button type="submit" name="action_type" value="selected" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف المنتجات المحددة؟')">
                    <i class="fas fa-trash"></i> Delete Selected Checked
                </button>

                <!-- الحذف المشروط الإجباري (يجب اختيار المخزن والتصنيف معاً) -->
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-danger small fw-bold"><i class="fas fa-exclamation-triangle"></i> Conditional Delete:</span>
                    
                    <select name="cond_store_id" class="form-select form-select-sm" required>
                        <option value="">* Select Store Required</option>
                        @foreach($stores ?? [] as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>

                    <select name="cond_category_id" class="form-select form-select-sm" required>
                        <option value="">* Select Category Required</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->translate('en')->name ?? 'Category' }}</option>
                        @endforeach
                    </select>

                    <button type="submit" name="action_type" value="conditional" class="btn btn-outline-danger btn-sm fw-bold" onclick="return confirm('تحذير صارم: سيتم حذف كافة المنتجات التابعة للمتجر والتصنيف المحددين معاً. هل أنت متأكد؟')">
                        <i class="fas fa-filter"></i> Delete By Store & Category
                    </button>
                </div>

            </div>
        </div>

        <!-- جدول عرض المنتجات -->
        <div class="card shadow mb-4 border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="ps-3">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>ID</th>
                                <th>Product Name (EN)</th>
                                <th>Code (ASIN/EAN)</th>
                                <th>Store</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th style="min-width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="ps-3">
                                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="form-check-input product-checkbox">
                                    </td>
                                    <td>{{ $product->id }}</td>
                                    <td class="fw-semibold text-truncate" style="max-width: 250px;">
                                        {{ $product->translate('en')->title ?? 'N/A' }}
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $product->product_code }}</span></td>
                                    <td>{{ $product->store->name ?? 'N/A' }}</td>
                                    <td>{{ $product->category->translate('en')->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    
                                    <!-- عمود الأسعار والأزرار (مطابق تماماً للصورة) -->
                                    <td>
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <!-- عرض السعر والنسبة -->
                                            <div>
                                                <div class="fw-bold text-success" style="font-size: 0.95rem;">
                                                    €{{ number_format($product->price, 2) }}
                                                </div>
                                                @if($product->original_price && $product->original_price > $product->price)
                                                    <div class="text-muted text-decoration-line-through small" style="font-size: 0.75rem;">
                                                        €{{ number_format($product->original_price, 2) }}
                                                    </div>
                                                @endif
                                                @if($product->discount_percentage > 0)
                                                    <div class="badge bg-danger text-white mt-1 px-1 py-0" style="font-size: 0.65rem;">
                                                        {{ $product->discount_percentage }}% OFF
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- أزرار الإجراءات (تعديل وحذف فردي) -->
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary px-2 py-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No products found matching your criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

</div>

<script>
    document.getElementById('select-all').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection