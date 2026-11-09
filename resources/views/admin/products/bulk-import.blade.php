@extends('layouts.admin')

@section('content')
    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold"><i class="fas fa-cloud-download-alt text-success"></i> Bulk Import Products from Amazon
            </h4>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.products.bulk-import.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        {{-- 1. اختيار المخزن (Store) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Store *</label>
                            <select name="store_id" class="form-select" required>
                                <option value="">Select Store...</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2. اختيار التصنيف (Category) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category...</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->translate('en')->name ?? 'Unnamed' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. كلمة البحث (Search Keyword) --}}
                        {{-- <div class="col-md-8">
                            <label class="form-label fw-bold">Search Keyword / Category (e.g. Headphones)</label>
                            <input type="text" name="query" class="form-control" 
                                placeholder="e.g. Wireless Earbuds">
                            <small class="text-muted">Enter keywords to search on Amazon and fetch matching
                                products.</small>
                        </div> --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category ID (Node ID)</label>
                          <input type="text" name="category_id_node" class="form-control" required placeholder="e.g. 16223503103">
                            <small class="text-muted">Get this ID from the URL of the category page on Store
                                (node=XXXXX).</small>
                        </div>
                        {{-- 4. عدد المنتجات (Number of Products) --}}
<div class="col-md-4">
    <label class="form-label fw-bold">Number of Products</label>
    <input type="number" name="limit" class="form-control" value="1" min="1" max="100" required>
    <small class="text-muted">Enter the number of products to import (e.g. 10).</small>
</div>

                        {{-- زر التنفيذ --}}
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-success px-5 fw-bold">
                                <i class="fas fa-download me-2"></i> Start Bulk Import
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
