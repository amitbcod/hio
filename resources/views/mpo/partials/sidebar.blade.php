@php
    $mpoStaffUser = $mpoStaffUser ?? request()->attributes->get('mpo_staff_user');
    $moduleForRoute = [
        'mpo.profile' => 'profile',
        'mpo.compliances' => 'compliance',
        'mpo.users' => 'users',
        'mpo.capacity-permissions' => 'capacity-permissions',
        'mpo.collaboration-agreements' => 'collaboration-agreements',
        'mpo.accounting-commission' => 'accounting',
        'mpo.accounting-payouts' => 'accounting',
    ];
    $mpoNavLinks = [
        ['label' => 'Profile', 'route' => 'mpo.profile', 'active' => 'mpo.profile', 'icon' => 'fa-building'],
        ['label' => 'Compliances', 'route' => 'mpo.compliances', 'active' => 'mpo.compliances', 'icon' => 'fa-file-shield'],
        ['label' => 'Users', 'route' => 'mpo.users', 'active' => 'mpo.users', 'icon' => 'fa-users'],
        ['label' => 'Capacity Permissions and Roles', 'route' => 'mpo.capacity-permissions', 'active' => 'mpo.capacity-permissions', 'icon' => 'fa-user-shield'],
        ['label' => 'Collaboration Agreements', 'route' => 'mpo.collaboration-agreements', 'active' => 'mpo.collaboration-agreements', 'icon' => 'fa-handshake'],
        ['label' => 'Accounting Commission', 'route' => 'mpo.accounting-commission', 'active' => 'mpo.accounting-commission', 'icon' => 'fa-percent'],
        ['label' => 'Accounting Payouts', 'route' => 'mpo.accounting-payouts', 'active' => 'mpo.accounting-payouts', 'icon' => 'fa-money-bill-transfer'],
    ];
@endphp

<nav class="list-group admin-mpo-nav" aria-label="MPO onboarding sections">
    @foreach($mpoNavLinks as $link)
        @if(! $mpoStaffUser || app(\App\Services\MpoStaffPermissionService::class)->can($mpoStaffUser, $moduleForRoute[$link['route']], 'read'))
        <a
            href="{{ route($link['route']) }}"
            class="list-group-item list-group-item-action{{ request()->routeIs($link['active']) ? ' active' : '' }}"
            @if(request()->routeIs($link['active'])) aria-current="page" @endif
        >
            <i class="fa-solid {{ $link['icon'] }} me-2" aria-hidden="true"></i>{{ $link['label'] }}
            @if($link['route'] === 'mpo.profile' && ! $mpoAdmin->hasCompleteMpoProfile())
                <span class="badge text-bg-warning ms-2">Incomplete</span>
            @endif
            @if($link['route'] === 'mpo.compliances')
                @php
                    $complianceStatus = $mpoAdmin->compliance?->verification_status;
                @endphp
                <span class="badge ms-2 {{ $complianceStatus === 'verified' ? 'text-bg-success' : ($complianceStatus === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') }}">
                    {{ $complianceStatus === 'verified' ? 'Verified' : ($complianceStatus === 'rejected' ? 'Rejected' : ($complianceStatus ? 'Pending Verification' : 'Incomplete')) }}
                </span>
            @endif
            @if($link['route'] === 'mpo.collaboration-agreements')
                @php
                    $agreement = $mpoAdmin->collaborationAgreements()->latest('id')->first();
                @endphp
                <span class="badge ms-2 {{ $agreement?->isOnboardingComplete() ? 'text-bg-success' : 'text-bg-warning' }}">
                    {{ $agreement?->isOnboardingComplete() ? 'Completed' : ($agreement ? 'In Progress' : 'Not Started') }}
                </span>
            @endif
            @if($link['route'] === 'mpo.accounting-commission')
                @php
                    $accountingConfig = $mpoAdmin->accountingConfiguration;
                    $bankDetails = $accountingConfig?->bank_details ?? [];
                    $accountingComplete = app(\App\Services\MpoAccountingService::class)->isComplete(
                        $accountingConfig?->vat_number,
                        (bool) $accountingConfig?->vat_exempted,
                        $mpoAdmin->country?->vat_percentage,
                        $bankDetails,
                    );
                @endphp
                <span class="badge ms-2 {{ $accountingComplete ? 'text-bg-success' : 'text-bg-warning' }}">
                    {{ $accountingComplete ? 'Completed' : ($accountingConfig ? 'In Progress' : 'Not Started') }}
                </span>
            @endif
            @if($link['route'] === 'mpo.accounting-payouts')
                @php
                    $payoutCompletion = app(\App\Services\MpoPayoutConfigurationService::class)->completionState($mpoAdmin);
                @endphp
                <span class="badge ms-2 {{ $payoutCompletion['complete'] ? 'text-bg-success' : 'text-bg-warning' }}">
                    {{ $payoutCompletion['complete'] ? 'Completed' : ($mpoAdmin->payoutConfiguration ? 'In Progress' : 'Not Started') }}
                </span>
            @endif
        </a>
        @endif
    @endforeach
    @if($mpoStaffUser)
        @foreach(config('mpo_staff.registry', []) as $module => $definition)
            @if(app(\App\Services\MpoStaffPermissionService::class)->can($mpoStaffUser, $module, 'read'))
                <a
                    href="{{ route('mpo.operations.module', ['module' => $module]) }}"
                    class="list-group-item list-group-item-action{{ request()->routeIs('mpo.operations.module') && request()->route('module') === $module ? ' active' : '' }}"
                    @if(request()->routeIs('mpo.operations.module') && request()->route('module') === $module) aria-current="page" @endif
                >{{ $definition['name'] }}</a>
            @endif
        @endforeach
    @endif
</nav>
