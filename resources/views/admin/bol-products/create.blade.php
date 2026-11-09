@extends('layouts.admin')
@section('title', 'إضافة منتج Bol.com جديد')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="fw-bold mb-0"><i class="fas fa-plus-circle text-primary me-2"></i> إضافة منتج جديد عبر كود EAN من Bol.com</h4>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.bol-products.store') }}" method="POST">
                @csrf

                <!-- اختيار المتجر -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">المتجر (Store)</label>
                    <select name="store_id" class="form-select" required>
                        <option value="">اختر المتجر...</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- اختيار التصنيف -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">التصنيف (Category)</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">اختر التصنيف...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->translate('en')?->name ?? $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- كود المنتج EAN -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">رمز المنتج (EAN / Product Code)</label>
                    <input type="text" name="product_code" class="form-control" placeholder="أدخل كود الـ EAN الخاص بـ بول..." required value="{{ old('product_code') }}">
                    <div class="form-text">سيقوم النظام بالاتصال بـ Bol.com API وجلب كافة التفاصيل والترجمات والسيو تلقائياً.</div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.bol-products.index') }}" class="btn btn-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">جلب وإضافة المنتج</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection