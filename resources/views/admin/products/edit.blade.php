@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Product: {{ $product->translate('en')->title ?? '' }}</h4>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- العمود الأيسر: الترجمات والسيو --}}
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Translations & SEO</h5>
                    </div>
                    <div class="card-body">
                        
                        <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
                            @foreach($locales as $index => $locale)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                            id="{{ $locale }}-tab" data-bs-toggle="tab" 
                                            data-bs-target="#{{ $locale }}-content" type="button" role="tab">
                                        {{ strtoupper($locale) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="productTabsContent">
                            @foreach($locales as $index => $locale)
                                @php
                                    $translation = $product->translate($locale);
                                @endphp
                                
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $locale }}-content" role="tabpanel">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Product Name ({{ strtoupper($locale) }})</label>
                                            <input type="text" name="{{ $locale }}[title]" class="form-control" value="{{ old($locale . '.title', $translation ? $translation->title : '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Slug / URL ({{ strtoupper($locale) }})</label>
                                            <input type="text" name="{{ $locale }}[slug]" class="form-control" value="{{ old($locale . '.slug', $translation ? $translation->slug : '') }}">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label text-muted">Description ({{ strtoupper($locale) }})</label>
                                        <textarea name="{{ $locale }}[content]" class="form-control rich-editor" rows="8">{{ old($locale . '.content', $translation ? $translation->content : '') }}</textarea>
                                    </div>

                                    <hr>
                                    
                                    <h6 class="text-primary mb-3"><i class="fas fa-search"></i> SEO Settings</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Meta Title</label>
                                        <input type="text" name="{{ $locale }}[meta_title]" class="form-control" placeholder="Max 60 characters" value="{{ old($locale . '.meta_title', $translation ? $translation->meta_title : '') }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Meta Description</label>
                                        <textarea name="{{ $locale }}[meta_description]" class="form-control" rows="3" placeholder="Max 160 characters">{{ old($locale . '.meta_description', $translation ? $translation->meta_description : '') }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">Meta Keywords</label>
                                        <input type="text" name="{{ $locale }}[meta_keywords]" class="form-control" placeholder="keyword1, keyword2..." value="{{ old($locale . '.meta_keywords', $translation ? $translation->meta_keywords : '') }}">
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- العمود الأيمن: الإعدادات العامة --}}
            <div class="col-lg-4">
                
                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Publish</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}>
                            
                            <label class="form-check-label" for="is_active">Active (Visible)</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sync-alt"></i> Update Product</button>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">General Info</h6>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Store *</label>
                            <select name="store_id" class="form-select" required>
                                <option value="">Select Store...</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ $product->store_id == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->translate('en')->name ?? 'Unnamed' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Code (ASIN/EAN) *</label>
                            <input type="text" name="product_code" class="form-control" required value="{{ old('product_code', $product->product_code) }}">
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/kzl9w89rnczir65eg99j1wr39vyt8latm23nuow87rg1bzyh/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '.rich-editor',
        height: 300,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
    });

    document.addEventListener('DOMContentLoaded', function() {
        const locales = @json($locales);
        locales.forEach(locale => {
            const titleInput = document.querySelector(`input[name="${locale}[title]"]`);
            const slugInput = document.querySelector(`input[name="${locale}[slug]"]`);
            if(titleInput && slugInput) {
                titleInput.addEventListener('input', function() {
                    if(!slugInput.value || slugInput.value === slugInput.dataset.auto) {
                        let slug = this.value.trim().toLowerCase()
                            .replace(/[\s_]+/g, '-')
                            .replace(/[^\w\u0621-\u064A\u011E\u011F\u0130\u0131\u00D6\u00F6\u00C7\u00E7\u015E\u015F\u00DC\u00FC-]+/g, '')
                            .replace(/--+/g, '-');
                        slugInput.value = slug;
                        slugInput.dataset.auto = slug; 
                    }
                });
            }
        });
    });
</script>
@endsection