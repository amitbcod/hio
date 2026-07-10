<!-- Transport Setup Wizard Sidebar -->
<div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
    <h6 style="font-weight:700;margin:0 0 16px 0;font-size:14px;color:#333;">Transport Setup</h6>
    <div style="display:flex;flex-direction:column;gap:8px;">
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
            <a href="{{ $routeUrl }}" style="padding:10px 12px;background:{{ $isActive ? '#e3f2fd' : '#f5f5f5' }};border-left:4px solid {{ $isActive ? '#2196f3' : '#ccc' }};border-radius:4px;text-decoration:none;font-size:13px;color:{{ $isActive ? '#2196f3' : '#666' }};font-weight:{{ $isActive ? '600' : '500' }};display:flex;justify-content:space-between;align-items:center;">
                <span>Step {{ $stepNumber }}: {{ $stepData['name'] }}</span>
                <span style="font-size:12px;">{{ $stepNumber }}</span>
            </a>
        @endforeach
    </div>
</div>
