<!-- Transport Setup Wizard Sidebar -->
<div style="">
    <h6 style="color:#fff">Transport Setup</h6>
    <div style="display:flex;flex-direction:column;gap:0px;">
        @php
            $steps = [
                1 => ['name' => 'Basic details', 'route' => 'operator.transport.basic-details'],
                2 => ['name' => 'Accounting and Transaction', 'route' => 'operator.transport.accounting-and-transaction'],
                3 => ['name' => 'Policies Rules', 'route' => 'operator.transport.policies-rules'],
                4 => ['name' => 'Reservation and Communication', 'route' => 'operator.transport.reservation-and-communication'],
            ];
            $currentStep = $step ?? 1;
        @endphp
        @foreach($steps as $stepNumber => $stepData)
            @php
                $isActive = $currentStep === $stepNumber;
                $routeUrl = route($stepData['route']);
            @endphp
            <a href="{{ $routeUrl }}" style="padding:10px 12px;background:{{ $isActive ? '#1e5f83' : 'transparent' }};border-left:0px solid {{ $isActive ? '#2196f3' : '#ccc' }};border-radius:0px;text-decoration:none;font-size:13px;color:{{ $isActive ? '#9ddcff' : '#fff' }};font-weight:{{ $isActive ? '600' : '500' }};display:flex;justify-content:space-between;align-items:center;border-bottom: 1px solid #477993;">
                <span>Step {{ $stepNumber }}: {{ $stepData['name'] }}</span>
                <span style="font-size:12px;">{{ $stepNumber }}</span>
            </a>
        @endforeach
    </div>
</div>
