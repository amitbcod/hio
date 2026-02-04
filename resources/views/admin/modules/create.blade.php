@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Create Module</h1>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.modules.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name') }}" required />
            </div>
            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input name="slug" class="form-control" value="{{ old('slug') }}" required />
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>
            <button class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
