<!-- Accommodation Steps Sidebar -->
<div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
    <h6 style="font-weight:700;margin:0 0 16px 0;font-size:14px;color:#333;">Accommodation Steps</h6>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @php
            $steps = [
                1 => ['name' => 'Basics', 'field' => 'step1_basics'],
                2 => ['name' => 'Reservation', 'field' => 'step2_legal'],
                3 => ['name' => 'Photos', 'field' => 'step3_media'],
                4 => ['name' => 'Compliance', 'field' => 'step7_compliance'],
                5 => ['name' => 'Accounting', 'field' => 'step5_rates'],
                6 => ['name' => 'Policies', 'field' => 'step6_policies'],
                7 => ['name' => 'Rooms', 'field' => 'step4_rooms'],
                8 => ['name' => 'Rate Plans', 'field' => 'step8_rates'],
                9 => ['name' => 'Pricing', 'field' => 'step9_pricing'],
                10 => ['name' => 'Inventory', 'field' => 'step10_inventory_allotment'],
                11 => ['name' => 'Promotions', 'field' => 'step11_promotions_offers'],
                12 => ['name' => 'SEO & Social', 'field' => 'step12_review'],
                13 => ['name' => 'Publish', 'field' => 'step13_publish'],
            ];
            $currentStep = isset($currentStep) ? $currentStep : null;
        @endphp
        @foreach($steps as $stepNum => $stepData)
            @php
                $isComplete = $accommodation->{$stepData['field']} ?? false;
                $isActive = ($currentStep === $stepNum);
                // Generate correct route name
                if ($stepNum == 1) {
                    $routeName = 'operator.accommodation.step1.edit';
                } else {
                    $routeName = 'operator.accommodation.step' . $stepNum . '.show';
                }
            @endphp
            <a href="{{ route($routeName, $accommodation->id) }}" style="padding:10px 12px;background:{{ $isActive ? '#e3f2fd' : ($isComplete ? '#e8f5e9' : '#f5f5f5') }};border-left:4px solid {{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#ccc') }};border-radius:4px;text-decoration:none;font-size:13px;color:{{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#666') }};font-weight:{{ $isActive ? '600' : '500' }};display:flex;justify-content:space-between;align-items:center;">
                <span>Step {{ $stepNum }}: {{ $stepData['name'] }}</span>
                <span style="font-size:12px;">{{ $isComplete ? '✓' : $stepNum }}</span>
            </a>
        @endforeach
    </div>
</div>
