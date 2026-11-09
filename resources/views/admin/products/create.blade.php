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

    <form action="{{ route('admin.products.store') }}" method="POST">
        @csrf
        
        <div class="row">
            {{-- العمود الأيسر: الترجمات والسيو (مساحة 8 أعمدة) --}}
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Translations & SEO</h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- شريط التبويبات --}}
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
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $locale }}-content" role="tabpanel">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Product Name ({{ strtoupper($locale) }})</label>
                                            <input type="text" name="{{ $locale }}[title]" class="form-control" value="{{ old($locale . '.title') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Slug / URL ({{ strtoupper($locale) }})</label>
                                            <input type="text" name="{{ $locale }}[slug]" class="form-control" value="{{ old($locale . '.slug') }}">
                                        </div>
                                    </div>

                                    {{-- محرر النصوص (WYSIWYG) --}}
                                    <div class="mb-4">
                                        <label class="form-label text-muted">Description ({{ strtoupper($locale) }})</label>
                                        <textarea name="{{ $locale }}[content]" class="form-control rich-editor" rows="8">{{ old($locale . '.content') }}</textarea>
                                    </div>

                                    <hr>
                                    
                                    {{-- إعدادات السيو --}}
                                    <h6 class="text-primary mb-3"><i class="fas fa-search"></i> SEO Settings</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Meta Title</label>
                                        <input type="text" name="{{ $locale }}[meta_title]" class="form-control" placeholder="Max 60 characters" value="{{ old($locale . '.meta_title') }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Meta Description</label>
                                        <textarea name="{{ $locale }}[meta_description]" class="form-control" rows="3" placeholder="Max 160 characters">{{ old($locale . '.meta_description') }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">Meta Keywords</label>
                                        <input type="text" name="{{ $locale }}[meta_keywords]" class="form-control" placeholder="keyword1, keyword2..." value="{{ old($locale . '.meta_keywords') }}">
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- العمود الأيمن: النشر والمعلومات العامة (مساحة 4 أعمدة) --}}
            <div class="col-lg-4">
                
                {{-- مربع النشر --}}
                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Publish</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active (Visible)</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Product</button>
                    </div>
                </div>

                {{-- مربع المعلومات العامة --}}
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
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->translate('en')->name ?? 'Unnamed' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Code (ASIN/EAN) *</label>
                            <input type="text" name="product_code" class="form-control" required placeholder="e.g. B08N5WRWNW">
                            <small class="text-muted">The system will fetch Price and Image using this code.</small>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
{{-- استدعاء محرر النصوص TinyMCE من CDN مجاني --}}
<script src="https://cdn.tiny.cloud/1/kzl9w89rnczir65eg99j1wr39vyt8latm23nuow87rg1bzyh/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // تفعيل المحرر على أي Textarea يمتلك كلاس rich-editor
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

    // السكريبت الخاص بتوليد الـ Slug (نفس المستخدم في التصنيفات)
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
    // التعبئة التلقائية لحقول السيو بناءً على العنوان والوصف لكل لغة
    document.addEventListener('DOMContentLoaded', function() {
        const locales = @json($locales);
        
        locales.forEach(locale => {
            const titleInput = document.querySelector(`input[name="${locale}[title]"]`);
            const metaTitleInput = document.querySelector(`input[name="${locale}[meta_title]"]`);
            const metaKeywordsInput = document.querySelector(`input[name="${locale}[meta_keywords]"]`);
            
            // Textarea أو محرر الوصف لكل لغة
            const descTextarea = document.querySelector(`textarea[name="${locale}[content]"]`);
            const metaDescInput = document.querySelector(`textarea[name="${locale}[meta_description]"]`);

            if (titleInput) {
                titleInput.addEventListener('input', function() {
                    let val = this.value;

                    // 1. تعبئة Meta Title تلقائياً إذا كان فارغاً أو كان يتبع القيمة التلقائية السابقة
                    if (metaTitleInput && (!metaTitleInput.value || metaTitleInput.value === metaTitleInput.dataset.auto)) {
                        metaTitleInput.value = val;
                        metaTitleInput.dataset.auto = val;
                    }

                    // 2. تعبئة Meta Keywords تلقائياً (تحويل المسافات إلى فواصل بين الكلمات)
                    if (metaKeywordsInput && (!metaKeywordsInput.value || metaKeywordsInput.value === metaKeywordsInput.dataset.auto)) {
                        let keywords = val.trim().split(/\s+/).filter(Boolean).join(', ');
                        metaKeywordsInput.value = keywords;
                        metaKeywordsInput.dataset.auto = keywords;
                    }
                });
            }

            // التعامل مع الوصف ومحرر TinyMCE لإزالة التنسيقات عند وضعه في Meta Description
if (descTextarea) {
                // دالة لتحديث الوصف مع إزالة الأسطر والفراغات المتعددة ودمجها في سطر واحد
                const updateMetaDesc = () => {
                    let content = "";
                    
                    // 1. جلب النص من محرر TinyMCE أو حقل النص العادي
                    if (typeof tinymce !== 'undefined' && tinymce.get(descTextarea.id || descTextarea.name)) {
                        content = tinymce.get(descTextarea.id || descTextarea.name).getContent({ format: 'text' });
                    } else {
                        let tmp = document.createElement("DIV");
                        tmp.innerHTML = descTextarea.value;
                        content = tmp.textContent || tmp.innerText || "";
                    }

                    // 2. إزالة الأسطر الجديدة (Newlines) والفراغات المتعددة ودمج النص بمسافة واحدة فقط
                    content = content.replace(/[\r\n]+/g, ' ').replace(/\s+/g, ' ').trim();

                    // 3. تعبئة الحقل تلقائياً
                    if (metaDescInput && (!metaDescInput.value || metaDescInput.value === metaDescInput.dataset.auto)) {
                        metaDescInput.value = content;
                        metaDescInput.dataset.auto = content;
                    }
                };

                // الاستماع لتغييرات المحرر
                setTimeout(() => {
                    if (typeof tinymce !== 'undefined') {
                        let editor = tinymce.get(descTextarea.id || descTextarea.name);
                        if (editor) {
                            editor.on('input keyup change', function() {
                                updateMetaDesc();
                            });
                        }
                    }
                }, 1000);

                descTextarea.addEventListener('input', updateMetaDesc);
            }
        });
    });
</script>
@endsection