@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Social Media Links</h2>
        <a href="{{ route('admin.social-media-links.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Link
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="10%">Icon</th>
                        <th>Platform Name</th>
                        <th>Profile URL</th>
                        <th width="10%">Status</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $link)
                    <tr>
                        <td>{{ $link->id }}</td>
                        <td class="text-center">
                            @if($link->icon)
                                <i class="{{ $link->icon }} fa-2x"></i>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $link->name ?? 'N/A' }}</td>
                        <td>
                            @if($link->url)
                                <a href="{{ $link->url }}" target="_blank">{{ $link->url }}</a>
                            @else
                                <span class="text-muted">No URL for current language</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $link->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $link->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.social-media-links.edit', $link->id) }}" class="btn btn-sm btn-info">Edit</a>
                            <form action="{{ route('admin.social-media-links.destroy', $link->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this link?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No social media links found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection