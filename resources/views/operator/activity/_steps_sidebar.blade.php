<!-- Activity Steps Sidebar -->
<div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
    <h6 style="font-weight:700;margin:0 0 16px 0;font-size:14px;color:#333;">Activity Steps</h6>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @php
            $steps = [
                1 => ['name' => 'Basics', 'field' => 'step1_basic'],
                2 => ['name' => 'Management', 'field' => 'step2_management_communication'],
                3 => ['name' => 'Photos', 'field' => 'step3_photos_media'],
                4 => ['name' => 'Legal', 'field' => 'step4_legal_compliance'],
                5 => ['name' => 'Accounting', 'field' => 'step5_accounting_transaction'],
                6 => ['name' => 'Policies', 'field' => 'step6_policies_rules'],
                7 => ['name' => 'Variants', 'field' => 'step7_variants_equipment'],
                8 => ['name' => 'TimeSlots', 'field' => 'step8_scheduling_timeslots'],
                9 => ['name' => 'Rates', 'field' => 'step9_rates'],
                10 => ['name' => 'Allotment', 'field' => 'step10_allotment'],
                11 => ['name' => 'Promotions', 'field' => 'step11_promotions_offers'],
                12 => ['name' => 'SEO & Social', 'field' => 'step12_seo_social'],
                13 => ['name' => 'Publish', 'field' => 'step13_publish'],
            ];
            $currentStep = isset($currentStep) ? $currentStep : null;
        @endphp
        @foreach($steps as $stepNum => $stepData)
            @php
                $isComplete = $activity->{$stepData['field']} ?? false;
                $isActive = ($currentStep === $stepNum);
                // Generate correct route name
                $routeName = 'operator.activity.step' . $stepNum . '.show';
            @endphp
            <a href="{{ route($routeName, $activity->id) }}" style="padding:10px 12px;background:{{ $isActive ? '#e3f2fd' : ($isComplete ? '#e8f5e9' : '#f5f5f5') }};border-left:4px solid {{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#ccc') }};border-radius:4px;text-decoration:none;font-size:13px;color:{{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#666') }};font-weight:{{ $isActive ? '600' : '500' }};display:flex;justify-content:space-between;align-items:center;">
                <span>Step {{ $stepNum }}: {{ $stepData['name'] }}</span>
                <span style="font-size:12px;">{{ $isComplete ? '✓' : $stepNum }}</span>
            </a>
        @endforeach
    </div>
</div>
