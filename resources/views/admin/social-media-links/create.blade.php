@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Add Social Media Link</h2>
    </div>

    <form action="{{ route('admin.social-media-links.store') }}" method="POST">
        @csrf
        <div class="row">
            
            <!-- القسم الأيسر: الترجمات (الاسم والرابط) -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Platform Details (Translations)</h5>
                    </div>
                    <div class="card-body">
                        
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

                        <div class="tab-content" id="languageTabsContent">
                            @foreach(config('translatable.locales') as $index => $locale)
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="tab-{{ $locale }}" role="tabpanel">
                                    
                                    <div class="form-group mb-3">
                                        <label class="text-muted">Platform Name ({{ strtoupper($locale) }})</label>
                                        <input type="text" name="{{ $locale }}[name]" class="form-control" 
                                               placeholder="e.g., Facebook, فيسبوك" value="{{ old($locale . '.name') }}">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="text-muted">Profile URL ({{ strtoupper($locale) }})</label>
                                        <input type="url" name="{{ $locale }}[url]" class="form-control" 
                                               placeholder="https://..." value="{{ old($locale . '.url') }}">
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

            <!-- القسم الأيمن: الإعدادات العامة -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Publish & Icon</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-4 form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label ms-2" for="is_active">Active (Visible)</label>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label class="text-muted">Icon Class (FontAwesome)</label>
                            <input type="text" name="icon" class="form-control" placeholder="e.g., fab fa-facebook" value="{{ old('icon') }}">
                            <small class="text-muted">Find icons at fontawesome.com</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Link</button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection