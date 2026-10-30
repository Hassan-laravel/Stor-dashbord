@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">

    {{-- رسائل النجاح --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h4 class="mb-0">Categories</h4>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Category
            </a>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name (EN)</th>
                            <th>Slug</th>
                            <th>Parent Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                
                                {{-- جلب الاسم باللغة الإنجليزية كعرض افتراضي في الجدول --}}
                                <td class="fw-bold">{{ $category->translate('en')->name ?? 'N/A' }}</td>
                                
                                <td>{{ $category->translate('en')->slug ?? 'N/A' }}</td>
                                
                                <td>
                                    @if($category->parent_id)
                                        <span class="badge bg-info text-dark">
                                            {{ $category->parent->translate('en')->name ?? 'Unknown' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Main</span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <div class="btn-group" role="group">
                                        {{-- زر التعديل --}}
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        {{-- زر الحذف --}}
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ترقيم الصفحات (Pagination) --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection