@extends('layouts.admin')

@section('content')
    <div class="container-fluid mt-4">

        {{-- رسائل الخطأ --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h4 class="mb-0">Edit Category: {{ $category->translate('en')->name ?? 'Category' }}</h4>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>
            </div>

            <div class="card-body">
                {{-- تغيير مسار الـ Form وإضافة طريقة العرض PUT --}}
                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- القسم الأول: الحقول الثابتة --}}
                    <div class="row mb-4 bg-light p-3 rounded">
                        <div class="col-md-4">
                            <label for="parent_id" class="form-label fw-bold">نوع التصنيف (Category Type)</label>
                            <select name="parent_id" id="parent_id" class="form-select">
                                <option value="">تصنيف رئيسي (Main Category)</option>
                                @foreach ($mainCategories as $main)
                                    <option value="{{ $main->id }}"
                                        {{ $category->parent_id == $main->id ? 'selected' : '' }}>
                                        -- تصنيف فرعي تابع لـ: {{ $main->translate('en')->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اتركه فارغاً إذا كان هذا تصنيفاً رئيسياً.</small>
                        </div>

                        {{-- حقل المتاجر مع تحديد المرتبط منها مسبقاً --}}
                        <div class="col-md-5">
                            <label for="stores" class="form-label fw-bold">المتاجر المرتبطة (Stores)</label>
                            <select name="stores[]" id="stores" class="form-select" multiple size="3">
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}"
                                        {{ in_array($store->id, old('stores', $attachedStores)) ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اضغط CTRL لتحديد أكثر من متجر.</small>
                        </div>

                        <div class="col-md-3 d-flex align-items-center mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                    value="1" {{ $category->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">مفعل (Active)</label>
                            </div>
                        </div>
                    </div>

                    {{-- القسم الثاني: قسم الترجمات (Translations) --}}
                    <h5 class="mb-3">Translations</h5>

                    {{-- شريط التبويبات (Tabs Nav) --}}
                    <ul class="nav nav-tabs mb-4" id="categoryTabs" role="tablist">
                        @foreach ($locales as $index => $locale)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ $locale }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#{{ $locale }}-content" type="button"
                                    role="tab">
                                    {{ strtoupper($locale) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- محتوى التبويبات (Tabs Content) --}}
                    <div class="tab-content" id="categoryTabsContent">
                        @foreach ($locales as $index => $locale)
                            @php
                                // استدعاء الترجمة الخاصة باللغة الحالية إذا كانت موجودة في قاعدة البيانات
                                $translation = $category->translate($locale);
                            @endphp

                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                id="{{ $locale }}-content" role="tabpanel">

                                <div class="row mb-3">
                                    <!-- حقل اسم التصنيف -->
                                    <div class="col-md-6">
                                        <label class="form-label">Category Name ({{ strtoupper($locale) }})</label>
                                        {{-- استخدام الدالة old مع تمرير القيمة المخزنة كقيمة احتياطية --}}
                                        <input type="text" name="{{ $locale }}[name]" class="form-control"
                                            value="{{ old($locale . '.name', $translation ? $translation->name : '') }}">
                                    </div>

                                    <!-- حقل الرابط -->
                                    <div class="col-md-6">
                                        <label class="form-label">Slug / URL ({{ strtoupper($locale) }})</label>
                                        <input type="text" name="{{ $locale }}[slug]" class="form-control"
                                            value="{{ old($locale . '.slug', $translation ? $translation->slug : '') }}">
                                    </div>
                                </div>

                                <!-- حقل الوصف -->
                                <div class="mb-3">
                                    <label class="form-label">Description ({{ strtoupper($locale) }})</label>
                                    <textarea name="{{ $locale }}[description]" class="form-control" rows="4">{{ old($locale . '.description', $translation ? $translation->description : '') }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- زر التحديث --}}
                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-sync-alt"></i> Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locales = @json($locales);

            locales.forEach(locale => {
                const nameInput = document.querySelector(`input[name="${locale}[name]"]`);
                const slugInput = document.querySelector(`input[name="${locale}[slug]"]`);

                if (nameInput && slugInput) {
                    nameInput.addEventListener('input', function() {
                        // التوليد الآلي للرابط فقط إذا كان الرابط فارغاً
                        if (!slugInput.value || slugInput.value === slugInput.dataset.auto) {
                            let slug = this.value.trim().toLowerCase()
                                .replace(/[\s_]+/g, '-')
                                .replace(
                                    /[^\w\u0621-\u064A\u011E\u011F\u0130\u0131\u00D6\u00F6\u00C7\u00E7\u015E\u015F\u00DC\u00FC-]+/g,
                                    '')
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
