@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Create Post Category</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.post-categories.store') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </div>

                @foreach(config('translatable.locales') as $locale)
                    <h5 class="mt-4 text-uppercase text-primary">{{ $locale }} Translation</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label>Name ({{ strtoupper($locale) }}) <span class="text-danger">*</span></label>
                            <input type="text" name="{{ $locale }}[name]" class="form-control" value="{{ old($locale . '.name') }}" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label>Slug ({{ strtoupper($locale) }}) <small class="text-muted">Optional</small></label>
                            <input type="text" name="{{ $locale }}[slug]" class="form-control" value="{{ old($locale . '.slug') }}">
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-success mt-3">Save Category</button>
                <a href="{{ route('admin.post-categories.index') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection