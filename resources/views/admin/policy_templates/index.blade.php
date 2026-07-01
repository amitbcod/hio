@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Policy Templates - {{ ucfirst($service) }}</h3>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Policy Type</th>
                <th>Title</th>
                
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $t)
            <tr>
                <td>{{ $t->policy_type }}</td>
                <td>{{ $t->title }}</td>
                
                <td>
                    <a href="{{ route('admin.policy-templates.edit', $t) }}" class="btn btn-sm btn-secondary">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
