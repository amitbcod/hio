@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Modules</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a class="btn btn-primary mb-3" href="{{ route('admin.modules.create') }}">Create Module</a>

        @if($modules->count())
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $m)
                        <tr>
                            <td>{{ $m->name }}</td>
                            <td>{{ $m->slug }}</td>
                            <td>{{ $m->description }}</td>
                            <td>
                                <a class="btn btn-sm btn-secondary" href="{{ route('admin.modules.edit', $m->id) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.modules.destroy', $m->id) }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Delete module?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No modules found.</p>
        @endif
    </div>
@endsection
