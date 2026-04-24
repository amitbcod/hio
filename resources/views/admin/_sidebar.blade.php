<div class="list-group">
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->is('admin/dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('admin.roles.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/roles*') ? 'active' : '' }}">Roles</a>
    <a href="{{ route('admin.modules.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/modules*') ? 'active' : '' }}">Modules</a>
    <a href="{{ route('admin.operators.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/operators*') ? 'active' : '' }}">Operators</a>
    <a href="{{ route('admin.travellers.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/travellers*') ? 'active' : '' }}">Travellers</a>
    @if(Route::has('admin.businesses.index'))
        <a href="{{ route('admin.businesses.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/businesses*') ? 'active' : '' }}">Businesses</a>
    @else
        <div class="list-group-item list-group-item-action text-muted">Businesses</div>
    @endif
    <a href="{{ route('admin.accommodation.bookings') }}" class="list-group-item list-group-item-action {{ request()->is('admin/accommodation/bookings*') ? 'active' : '' }}">Accommodation Bookings</a>
    <a href="{{ route('admin.activity.bookings') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity/bookings*') ? 'active' : '' }}">Activity Bookings</a>
</div>