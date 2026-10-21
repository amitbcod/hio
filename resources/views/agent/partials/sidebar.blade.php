@php
    $agentPanelOwner = $agentPanelUser instanceof \App\Models\AgentAccount;
    $agentPanelLinks = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-gauge-high',
            'route' => 'agent.dashboard',
            'active' => 'agent.dashboard',
            'module' => null,
        ],
        [
            'label' => 'Agency Profile',
            'icon' => 'fa-building',
            'route' => 'agent.profile',
            'active' => 'agent.profile',
            'module' => 'profile',
        ],
        [
            'label' => 'Compliance',
            'icon' => 'fa-file-shield',
            'route' => 'agent.compliance',
            'active' => 'agent.compliance*',
            'module' => 'compliance',
        ],
        [
            'label' => 'Users',
            'icon' => 'fa-users',
            'route' => 'agent.users',
            'active' => 'agent.users*',
            'module' => 'users',
        ],
        [
            'label' => 'Operations',
            'icon' => 'fa-sliders',
            'route' => 'agent.operations',
            'active' => 'agent.operations*',
            'module' => 'operations',
        ],
        [
            'label' => 'Closed Group Bookings',
            'icon' => 'fa-suitcase',
            'route' => 'agent.closed-groups.index',
            'active' => 'agent.closed-groups.*',
            'module' => 'reservation',
        ],
    ];
@endphp

<nav class="agent-nav" aria-label="Agent navigation">
    <div class="agent-nav-label">Workspace</div>
    @foreach($agentPanelLinks as $link)
        @if($link['module'] === null || $agentPanelOwner || ($agentPanelUser instanceof \App\Models\AgentStaffUser && app(\App\Services\AgentPermissionService::class)->can($agentPanelUser, $link['module'], 'read')))
            <a
                class="agent-nav-link{{ request()->routeIs($link['active']) ? ' active' : '' }}"
                href="{{ route($link['route']) }}"
                @if(request()->routeIs($link['active'])) aria-current="page" @endif
            >
                <i class="fa-solid {{ $link['icon'] }}" aria-hidden="true"></i>
                <span>{{ $link['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
