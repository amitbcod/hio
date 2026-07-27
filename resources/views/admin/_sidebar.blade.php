<div class="list-group">
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->is('admin/dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('admin.settings.edit') }}" class="list-group-item list-group-item-action {{ request()->is('admin/settings*') ? 'active' : '' }}">Settings</a>
    <a href="{{ route('admin.roles.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/roles*') ? 'active' : '' }}">Roles</a>
    <a href="{{ route('admin.modules.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/modules*') ? 'active' : '' }}">Modules</a>
    <a href="{{ route('admin.operators.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/operators*') ? 'active' : '' }}">Operators</a>
    <a href="{{ route('admin.travellers.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/travellers*') ? 'active' : '' }}">Travellers</a>
    <a href="{{ route('admin.feedback.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/feedback*') ? 'active' : '' }}">Feedback</a>
    @if(Route::has('admin.businesses.index'))
        <!-- <a href="{{ route('admin.businesses.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/businesses*') ? 'active' : '' }}">Businesses</a> -->
    @else
        <!-- <div class="list-group-item list-group-item-action text-muted">Businesses</div> -->
    @endif
    <a href="{{ route('admin.accommodation.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/accommodations*') ? 'active' : '' }}">Accommodations</a>
    <!-- <a href="{{ route('admin.accommodation.create') }}" class="list-group-item list-group-item-action {{ request()->is('admin/accommodations/create*') ? 'active' : '' }}">Create Accommodation</a> -->
    <a href="{{ route('admin.activity.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity*') ? 'active' : '' }}">Activities</a>
    <!-- <a href="{{ route('admin.activity.create') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity/create*') ? 'active' : '' }}">Create Activity</a> -->
    <a href="{{ route('admin.accommodation.bookings') }}" class="list-group-item list-group-item-action {{ request()->is('admin/accommodation/bookings*') ? 'active' : '' }}">Accommodation Bookings</a>
    <a href="{{ route('admin.activity.bookings') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity/bookings*') ? 'active' : '' }}">Activity Bookings</a>
    <a href="{{ route('admin.transport.bookings') }}" class="list-group-item list-group-item-action {{ request()->is('admin/transport/bookings*') ? 'active' : '' }}">Transport Bookings</a>
    <a href="{{ route('admin.payment-transactions.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/payment-transactions*') ? 'active' : '' }}">Payment Transactions</a>
    <a href="{{ route('admin.shared-carts.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/shared-carts*') ? 'active' : '' }}">Shared Cart Links</a>
    @if(Route::has('admin.policy-templates.index'))
        <a href="{{ route('admin.policy-templates.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy-templates*') ? 'active' : '' }} font-weight-bold">HIO Policy Templates</a>
    @else
        <div class="list-group-item list-group-item-action mt-2 font-weight-bold">HIO Policy Templates</div>
    @endif
    <a href="{{ route('admin.policy-templates.index', ['service' => 'accommodation']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy-templates*') && request('service')=='accommodation' ? 'active' : '' }}">Accommodation</a>
    <a href="{{ route('admin.policy-templates.index', ['service' => 'activity']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy-templates*') && request('service')=='activity' ? 'active' : '' }}">Activity</a>
    <a href="{{ route('admin.vehicle-types.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/vehicle-types*') ? 'active' : '' }}">Vehicle Types</a>
    <a href="{{ route('admin.regions.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/regions*') ? 'active' : '' }}">Regions</a>
    <a href="{{ route('admin.transport-service-route-pairs.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/transport-service-route-pairs*') ? 'active' : '' }}">Transport Service Route Pairs</a>
    <a href="{{ route('admin.places.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/places*') ? 'active' : '' }}">Hotel / City Mapping</a>
    @if(Route::has('admin.static-pages.index'))
        <a href="{{ route('admin.static-pages.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/static-pages*') ? 'active' : '' }} font-weight-bold">Pages</a>
    @else
        <div class="list-group-item list-group-item-action mt-2 font-weight-bold">Pages</div>
    @endif
    <a href="{{ route('admin.static-pages.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/static-pages*') ? 'active' : '' }}">Static Pages</a>
</div>