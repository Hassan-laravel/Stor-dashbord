@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit {{ $post->type == 'page' ? 'Page' : 'Article' }}: {{ $post->title ?? 'Untitled' }}</h2>
    </div>

    <form action="{{ route('admin.posts.update', $post->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            
            <!-- القسم الأيسر: الترجمات ومحرر النصوص والسيو -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Translations & SEO</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- نظام التبويبات (Tabs) -->
                        <ul class="nav nav-tabs border-bottom-0 mb-4" id="languageTabs" role="tablist">
                            @foreach(config('translatable.locales') as $index => $locale)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $index === 0 ? 'active' : '' }} text-uppercase" 
                                            id="{{ $locale }}-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#tab-{{ $locale }}" 
                                            type="button" role="tab">
                                        {{ $locale }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <!-- محتوى التبويبات -->
                        <div class="tab-content" id="languageTabsContent">
                            @foreach(config('translatable.locales') as $index => $locale)
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="tab-{{ $locale }}" role="tabpanel">
                                    
                                    <div class="row">
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="text-muted">Title ({{ strtoupper($locale) }})</label>
                                            <input type="text" name="{{ $locale }}[title]" 
                                                   class="form-control title-input" 
                                                   data-locale="{{ $locale }}" 
                                                   value="{{ old($locale . '.title', $post->translate($locale)->title ?? '') }}">
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="text-muted">Slug / URL ({{ strtoupper($locale) }})</label>
                                            <input type="text" name="{{ $locale }}[slug]" 
                                                   class="form-control slug-input-{{ $locale }}" 
                                                   value="{{ old($locale . '.slug', $post->translate($locale)->slug ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="text-muted">Content ({{ strtoupper($locale) }})</label>
                                        <textarea name="{{ $locale }}[content]" class="form-control tinymce-editor">{{ old($locale . '.content', $post->translate($locale)->content ?? '') }}</textarea>
                                    </div>

                                    <hr>
                                    <h6 class="text-primary mb-3"><i class="fas fa-search"></i> SEO Settings</h6>
                                    
                                    <div class="form-group mb-3">
                                        <label class="text-muted">Meta Title ({{ strtoupper($locale) }})</label>
                                        <input type="text" name="{{ $locale }}[meta_title]" 
                                               class="form-control meta-title-input-{{ $locale }}" 
                                               value="{{ old($locale . '.meta_title', $post->translate($locale)->meta_title ?? '') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="text-muted">Meta Description ({{ strtoupper($locale) }})</label>
                                        <textarea name="{{ $locale }}[meta_description]" class="form-control" rows="2">{{ old($locale . '.meta_description', $post->translate($locale)->meta_description ?? '') }}</textarea>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

            <!-- القسم الأيمن: الشريط الجانبي للإعدادات -->
            <div class="col-md-4">
                
                <!-- صندوق النشر -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Publish</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-4 form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $post->is_active ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="is_active">Active (Visible)</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sync-alt"></i> Update Content</button>
                    </div>
                </div>

                <!-- صندوق المعلومات العامة -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">General Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="text-muted">Content Type *</label>
                            <select name="type" id="post_type" class="form-control" onchange="toggleCategory()">
                                <option value="article" {{ (old('type', $post->type) == 'article') ? 'selected' : '' }}>Article</option>
                                <option value="page" {{ (old('type', $post->type) == 'page') ? 'selected' : '' }}>Static Page</option>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="category_wrapper">
                            <label class="text-muted">Category *</label>
                            <select name="post_category_id" class="form-control">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('post_category_id', $post->post_category_id) == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- تضمين TinyMCE وسكربتات التبديل والتعبئة التلقائية -->
<script src="https://cdn.tiny.cloud/1/kzl9w89rnczir65eg99j1wr39vyt8latm23nuow87rg1bzyh/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // 1. إعداد محرر TinyMCE
    tinymce.init({
        selector: '.tinymce-editor',
        height: 300,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });

    // 2. دالة إخفاء وإظهار التصنيف بناءً على نوع المحتوى
    function toggleCategory() {
        var type = document.getElementById('post_type').value;
        var categoryWrapper = document.getElementById('category_wrapper');
        
        if (type === 'page') {
            categoryWrapper.style.display = 'none';
        } else {
            categoryWrapper.style.display = 'block';
        }
    }
    document.addEventListener("DOMContentLoaded", toggleCategory);

    // 3. التعبئة التلقائية للـ Slug والـ Meta Title
    document.addEventListener("DOMContentLoaded", function() {
        const titleInputs = document.querySelectorAll('.title-input');
        titleInputs.forEach(function(titleInput) {
            titleInput.addEventListener('input', function() {
                const locale = this.getAttribute('data-locale');
                const titleValue = this.value;

                const slugInput = document.querySelector('.slug-input-' + locale);
                const metaTitleInput = document.querySelector('.meta-title-input-' + locale);

                if (metaTitleInput) {
                    metaTitleInput.value = titleValue;
                }

                if (slugInput) {
                    const slugValue = titleValue.toLowerCase()
                        .replace(/[^\p{L}\p{N}\s-]/gu, '') 
                        .replace(/[\s_-]+/g, '-')          
                        .replace(/^-+|-+$/g, '');          
                    
                    slugInput.value = slugValue;
                }
            });
        });
    });
</script>
@endsection