@extends('layouts.admin')
@section('title', 'تعديل منتج Bol.com')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="fw-bold mb-0"><i class="fas fa-edit text-primary me-2"></i> تعديل منتج Bol.com</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.bol-products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">المتجر (Store)</label>
                    <select name="store_id" class="form-select" required>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $product->store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">التصنيف (Category)</label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->translate('en')?->name ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">رمز المنتج (EAN)</label>
                    <input type="text" name="product_code" class="form-control" value="{{ $product->product_code }}" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.bol-products.index') }}" class="btn btn-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection