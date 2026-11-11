@php
    $mpoStaffUser = $mpoStaffUser ?? request()->attributes->get('mpo_staff_user');
    $mpoAdmin = $mpoAdmin ?? request()->attributes->get('mpo_admin');
    $moduleForRoute = ['mpo.users' => 'users'];
    $mpoUserLinks = [
        ['label' => 'Users', 'route' => 'mpo.users', 'active' => 'mpo.users*', 'icon' => 'fa-users'],
    ];
    $mpoSetupLinks = [
        ['label' => 'Profile', 'route' => 'mpo.profile', 'active' => 'mpo.profile', 'icon' => 'fa-building'],
        ['label' => 'Compliances', 'route' => 'mpo.compliances', 'active' => 'mpo.compliances', 'icon' => 'fa-file-shield'],
        ['label' => 'Collaboration Agreements', 'route' => 'mpo.collaboration-agreements', 'active' => 'mpo.collaboration-agreements', 'icon' => 'fa-handshake'],
        ['label' => 'Accounting Commission', 'route' => 'mpo.accounting-commission', 'active' => 'mpo.accounting-commission', 'icon' => 'fa-percent'],
        ['label' => 'Accounting Payouts', 'route' => 'mpo.accounting-payouts', 'active' => 'mpo.accounting-payouts', 'icon' => 'fa-money-bill-transfer'],
        ['label' => 'Users', 'route' => 'mpo.users', 'active' => 'mpo.users*', 'icon' => 'fa-users'],
    ];
    $completionState = $mpoStaffUser
        ? []
        : app(\App\Services\MpoRegistrationCompletionService::class)->state($mpoAdmin);
    $profileComplete = $completionState['profile'] ?? false;
    $compliance = $completionState['complianceRecord'] ?? null;
    $agreement = $completionState['agreementRecord'] ?? null;
    $accountingConfig = $completionState['accountingRecord'] ?? null;
    $accountingComplete = $completionState['accounting'] ?? false;
    $payoutCompletion = null;
    if (! $mpoStaffUser) {
        $payoutCompletion = app(\App\Services\MpoPayoutConfigurationService::class)->completionState($mpoAdmin);
    }
@endphp

<div class="list-group admin-mpo-nav" role="navigation" aria-label="MPO navigation">
    @if(! $mpoStaffUser && ($completionState['complete'] ?? false))
        @include('admin._sidebar')
        <details class="list-group-item p-0 mt-2" @if(request()->routeIs('mpo.users*')) open @endif>
            <summary class="list-group-item list-group-item-action">MPO Setup</summary>
            <div class="list-group">
                @foreach($mpoSetupLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="list-group-item list-group-item-action{{ request()->routeIs($link['active']) ? ' active' : '' }}"
                       @if(request()->routeIs($link['active'])) aria-current="page" @endif>
                        <i class="fa-solid {{ $link['icon'] }} me-2" aria-hidden="true"></i>{{ $link['label'] }}
                        @if($link['route'] === 'mpo.profile')
                            <span class="badge text-bg-success ms-2">Completed</span>
                        @elseif($link['route'] === 'mpo.compliances')
                            <span class="badge ms-2 {{ $completionState['compliance'] ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $compliance?->verificationStatusLabel() ?? 'Incomplete' }}
                            </span>
                        @elseif($link['route'] === 'mpo.collaboration-agreements')
                            <span class="badge ms-2 {{ $completionState['agreement'] ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $completionState['agreement'] ? 'Completed' : ($agreement ? 'In Progress' : 'Not Started') }}
                            </span>
                        @elseif($link['route'] === 'mpo.accounting-commission')
                            <span class="badge ms-2 {{ $accountingComplete ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $accountingComplete ? 'Completed' : ($accountingConfig ? 'In Progress' : 'Not Started') }}
                            </span>
                        @elseif($link['route'] === 'mpo.accounting-payouts')
                            <span class="badge ms-2 {{ $payoutCompletion['complete'] ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $payoutCompletion['complete'] ? 'Completed' : ($mpoAdmin->payoutConfiguration ? 'In Progress' : 'Not Started') }}
                            </span>
                        @endif
                    </a>
                @endforeach
                <a href="{{ route('mpo.capacity-permissions') }}"
                   class="list-group-item list-group-item-action{{ request()->routeIs('mpo.capacity-permissions') ? ' active' : '' }}">
                    <i class="fa-solid fa-user-shield me-2" aria-hidden="true"></i>Capacity Permissions and Roles
                </a>
            </div>
        </details>
    @elseif($mpoStaffUser)
        @foreach($mpoUserLinks as $link)
            @if(app(\App\Services\MpoStaffPermissionService::class)->can($mpoStaffUser, $moduleForRoute[$link['route']], 'read'))
                <a href="{{ route($link['route']) }}"
                   class="list-group-item list-group-item-action{{ request()->routeIs($link['active']) ? ' active' : '' }}">
                    <i class="fa-solid {{ $link['icon'] }} me-2" aria-hidden="true"></i>{{ $link['label'] }}
                </a>
            @endif
        @endforeach
        @foreach(config('mpo_staff.registry', []) as $module => $definition)
            @if(app(\App\Services\MpoStaffPermissionService::class)->can($mpoStaffUser, $module, 'read'))
                <a href="{{ route('mpo.operations.module', ['module' => $module]) }}"
                   class="list-group-item list-group-item-action{{ request()->routeIs('mpo.operations.module') && request()->route('module') === $module ? ' active' : '' }}">
                    {{ $definition['name'] }}
                </a>
            @endif
        @endforeach
    @else
        <details class="list-group-item p-0" @if(request()->routeIs('mpo.users*')) open @endif>
            <summary class="list-group-item list-group-item-action">MPO Setup</summary>
            <div class="list-group">
                @foreach($mpoSetupLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="list-group-item list-group-item-action{{ request()->routeIs($link['active']) ? ' active' : '' }}">
                        <i class="fa-solid {{ $link['icon'] }} me-2" aria-hidden="true"></i>{{ $link['label'] }}
                        @if($link['route'] === 'mpo.profile')
                            <span class="badge ms-2 {{ $profileComplete ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $profileComplete ? 'Completed' : 'Incomplete' }}
                            </span>
                        @elseif($link['route'] === 'mpo.compliances')
                            <span class="badge ms-2 {{ $completionState['compliance'] ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $compliance?->verificationStatusLabel() ?? 'Incomplete' }}
                            </span>
                        @elseif($link['route'] === 'mpo.collaboration-agreements')
                            <span class="badge ms-2 {{ $completionState['agreement'] ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $completionState['agreement'] ? 'Completed' : ($agreement ? 'In Progress' : 'Not Started') }}
                            </span>
                        @elseif($link['route'] === 'mpo.accounting-commission')
                            <span class="badge ms-2 {{ $accountingComplete ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $accountingComplete ? 'Completed' : ($accountingConfig ? 'In Progress' : 'Not Started') }}
                            </span>
                        @elseif($link['route'] === 'mpo.accounting-payouts')
                            <span class="badge ms-2 {{ $payoutCompletion['complete'] ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $payoutCompletion['complete'] ? 'Completed' : ($mpoAdmin->payoutConfiguration ? 'In Progress' : 'Not Started') }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </details>
    @endif
</div>
