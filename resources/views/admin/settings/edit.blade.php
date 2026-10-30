@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>General Settings</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- القسم الأيسر: الإعدادات المترجمة (نظام التبويبات) -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Site Details (Translations)</h5>
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
                                        <label class="text-muted">Site Name ({{ strtoupper($locale) }})</label>
                                        <input type="text" name="{{ $locale }}[site_name]" class="form-control" 
                                               value="{{ old($locale . '.site_name', $setting->translate($locale)->site_name ?? '') }}">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="text-muted">Site Description / SEO ({{ strtoupper($locale) }})</label>
                                        <textarea name="{{ $locale }}[site_description]" class="form-control" rows="3">{{ old($locale . '.site_description', $setting->translate($locale)->site_description ?? '') }}</textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="text-muted">Address ({{ strtoupper($locale) }})</label>
                                        <input type="text" name="{{ $locale }}[address]" class="form-control" 
                                               value="{{ old($locale . '.address', $setting->translate($locale)->address ?? '') }}">
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
                
                <!-- روابط التواصل الاجتماعي -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Social Media Links</h5>
                    </div>
                    <div class="card-body row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="text-muted"><i class="fab fa-facebook text-primary"></i> Facebook</label>
                            <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $setting->facebook_url) }}">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="text-muted"><i class="fab fa-twitter text-info"></i> Twitter</label>
                            <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $setting->twitter_url) }}">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="text-muted"><i class="fab fa-instagram text-danger"></i> Instagram</label>
                            <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $setting->instagram_url) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- القسم الأيمن: الإعدادات العامة (الصور والاتصال) -->
            <div class="col-md-4">
                
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Save Actions</h6>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Settings</button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Logos & Images</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="text-muted">Site Logo</label>
                            <input type="file" name="logo" class="form-control mb-2" accept="image/*">
                            @if($setting->logo)
                                <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" class="img-thumbnail" style="max-height: 80px;">
                            @endif
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-muted">Favicon</label>
                            <input type="file" name="favicon" class="form-control mb-2" accept="image/x-icon,image/png">
                            @if($setting->favicon)
                                <img src="{{ asset('storage/' . $setting->favicon) }}" alt="Favicon" class="img-thumbnail" style="max-height: 40px;">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Contact Info (Global)</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="text-muted">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $setting->contact_email) }}">
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-muted">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $setting->contact_phone) }}">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection