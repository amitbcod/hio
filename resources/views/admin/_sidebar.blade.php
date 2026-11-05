<nav class="list-group admin-mpo-nav" aria-label="Admin and MPO sections">
    @php($isMpoAdmin = \App\Support\CountryAccess::isMpoAdmin())
    <a href="{{ $isMpoAdmin ? route('mpo.dashboard') : route('admin.dashboard') }}"
       class="list-group-item list-group-item-action {{ ($isMpoAdmin && request()->routeIs('mpo.dashboard')) || (! $isMpoAdmin && request()->routeIs('admin.dashboard')) ? 'active' : '' }}">Dashboard</a>
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.trips.index'))
        <a href="{{ route('admin.trips.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/trips*') ? 'active' : '' }}">Trips</a>
    @endif
    @unless($isMpoAdmin)
        <a href="{{ route('admin.settings.edit') }}" class="list-group-item list-group-item-action {{ request()->is('admin/settings*') ? 'active' : '' }}">Settings</a>
        <a href="{{ route('admin.country-admins.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/country-admins*') ? 'active' : '' }}">MPO Management</a>
        <a href="{{ route('admin.countries.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/countries*') ? 'active' : '' }}">Countries</a>
        <a href="{{ route('admin.roles.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/roles*') ? 'active' : '' }}">Roles</a>
        <a href="{{ route('admin.modules.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/modules*') ? 'active' : '' }}">Modules</a>
    @endunless
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.operators.index'))
        <a href="{{ route('admin.operators.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/operators*') ? 'active' : '' }}">Operators</a>
    @endif
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.agents.index'))
        <a href="{{ route('admin.agents.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/agents*') || request()->is('admin/agent-compliance*') ? 'active' : '' }}">Agents</a>
    @endif
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.travellers.index'))
        <a href="{{ route('admin.travellers.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/travellers*') ? 'active' : '' }}">Travellers</a>
    @endif
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.feedback.index'))
        <a href="{{ route('admin.feedback.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/feedback*') ? 'active' : '' }}">Feedback</a>
    @endif
    @if(! $isMpoAdmin && Route::has('admin.businesses.index'))
        <!-- <a href="{{ route('admin.businesses.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/businesses*') ? 'active' : '' }}">Businesses</a> -->
    @endif
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.accommodation.index'))
        <a href="{{ route('admin.accommodation.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/accommodations*') ? 'active' : '' }}">Accommodations</a>
    @endif
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.activity.index'))
        <a href="{{ route('admin.activity.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity*') ? 'active' : '' }}">Activities</a>
    @endif
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.packages.index'))
        <a href="{{ route('admin.packages.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/packages*') ? 'active' : '' }}">Packages</a>
    @endif
    @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar('admin.groups.index'))
        <a href="{{ route('admin.groups.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/groups*') ? 'active' : '' }}">Groups</a>
    @endif

    @unless($isMpoAdmin)
        <a href="{{ route('admin.closed-groups.book') }}" class="list-group-item list-group-item-action {{ request()->is('admin/closed-groups/book*') ? 'active' : '' }}">Closed Group Booking</a>
        <a href="{{ route('admin.closed-groups.settlement') }}" class="list-group-item list-group-item-action {{ request()->is('admin/closed-groups/bank-transfer-settlement*') ? 'active' : '' }}">Bank Transfer Settlement</a>
    @endunless
    @foreach([
        ['admin.accommodation.bookings', 'admin/accommodation/bookings*', 'Accommodation Bookings'],
        ['admin.activity.bookings', 'admin/activity/bookings*', 'Activity Bookings'],
        ['admin.transport.bookings', 'admin/transport/bookings*', 'Transport Bookings'],
        ['admin.payment-transactions.index', 'admin/payment-transactions*', 'Payment Transactions'],
    ] as [$routeName, $path, $label])
        @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar($routeName))
            <a href="{{ route($routeName) }}" class="list-group-item list-group-item-action {{ request()->is($path) ? 'active' : '' }}">{{ $label }}</a>
        @endif
    @endforeach
    @unless($isMpoAdmin)
        <a href="{{ route('admin.shared-carts.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/shared-carts*') ? 'active' : '' }}">Shared Cart Links</a>
        <div class="list-group-item list-group-item-action font-weight-bold">Policy</div>
        <a href="{{ route('admin.policy.package-default-policy') }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy/package-default-policy*') ? 'active' : '' }}">Package Default Policy</a>
        <a href="{{ route('admin.policy.group-default-policy') }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy/group-default-policy*') ? 'active' : '' }}">Group Default Policy</a>
        @if(Route::has('admin.policy-templates.index'))
            <a href="{{ route('admin.policy-templates.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy-templates*') ? 'active' : '' }} font-weight-bold">HIO Policy Templates</a>
        @endif
        <a href="{{ route('admin.policy-templates.index', ['service' => 'accommodation']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy-templates*') && request('service')=='accommodation' ? 'active' : '' }}">Accommodation</a>
        <a href="{{ route('admin.policy-templates.index', ['service' => 'activity']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/policy-templates*') && request('service')=='activity' ? 'active' : '' }}">Activity</a>
    @endunless
    @foreach([
        ['admin.vehicle-types.index', 'admin/vehicle-types*', 'Vehicle Types'],
        ['admin.vehicle-names.index', 'admin/vehicle-names*', 'Vehicle Names'],
        ['admin.regions.index', 'admin/regions*', 'Regions'],
        ['admin.transport-service-route-pairs.index', 'admin/transport-service-route-pairs*', 'Transport Service Route Pairs'],
        ['admin.places.index', 'admin/places*', 'Hotel / City Mapping'],
    ] as [$routeName, $path, $label])
        @if(! $isMpoAdmin || \App\Support\MpoAdminRouteAccess::showsInSidebar($routeName))
            <a href="{{ route($routeName) }}" class="list-group-item list-group-item-action {{ request()->is($path) ? 'active' : '' }}">{{ $label }}</a>
        @endif
    @endforeach
    @unless($isMpoAdmin)
        @if(Route::has('admin.static-pages.index'))
            <a href="{{ route('admin.static-pages.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/static-pages*') ? 'active' : '' }} font-weight-bold">Pages</a>
        @else
            <div class="list-group-item list-group-item-action mt-2 font-weight-bold">Pages</div>
        @endif
        <a href="{{ route('admin.static-pages.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/static-pages*') ? 'active' : '' }}">Static Pages</a>
    @endunless
</nav>
