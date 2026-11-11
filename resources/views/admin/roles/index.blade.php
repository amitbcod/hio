@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h3>Manage Roles</h3>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" class="form-control" maxlength="150" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Scope</label>
                        <select name="scope" class="form-select" id="role-scope" required>
                            <option value="global">Global</option>
                            <option value="country">Country</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="role-country-field" hidden>
                        <label class="form-label">Country</label>
                        <select name="country_id" class="form-select">
                            <option value="">Select a country</option>
                            @foreach(\App\Models\Country::orderBy('name')->get() as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary">Create Role</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Name</th><th>Scope</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" class="form-control" value="{{ $role->name }}" maxlength="150" required>
                                    <select name="scope" class="form-select" style="max-width: 150px" onchange="toggleRoleCountry(this)">
                                        <option value="global" @selected(($role->scope ?? 'business') === 'global')>Global</option>
                                        <option value="country" @selected(($role->scope ?? '') === 'country')>Country</option>
                                        @if(($role->scope ?? '') === 'business')
                                            <option value="country" selected>Country (legacy)</option>
                                        @endif
                                    </select>
                                    <select name="country_id" class="form-select role-country" style="max-width: 200px" @if(($role->scope ?? '') !== 'country') hidden @endif>
                                        <option value="">Select a country</option>
                                        @foreach(\App\Models\Country::orderBy('name')->get() as $country)
                                            <option value="{{ $country->id }}" @selected((int) $role->country_id === (int) $country->id)>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Save</button>
                                </form>
                            </td>
                            <td>{{ ($role->scope ?? 'business') === 'global' ? 'Global' : (($role->scope ?? '') === 'country' ? (\App\Models\Country::find($role->country_id)->name ?? 'Country') : 'Legacy business scope') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    const newScope = document.getElementById('role-scope');
    const newCountryField = document.getElementById('role-country-field');
    function updateNewRoleScope() {
        newCountryField.hidden = newScope.value !== 'country';
        newCountryField.querySelector('select').required = newScope.value === 'country';
    }
    newScope.addEventListener('change', updateNewRoleScope);
    updateNewRoleScope();
    function toggleRoleCountry(select) {
        const country = select.closest('form').querySelector('.role-country');
        country.hidden = select.value !== 'country';
        country.required = select.value === 'country';
    }
</script>
@endsection