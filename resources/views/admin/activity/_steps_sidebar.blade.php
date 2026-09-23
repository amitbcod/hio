<!-- Activity Steps Sidebar -->
<div style="">
    <h6 style="color:#fff">Activity Steps</h6>
    <div style="display:flex;flex-direction:column;gap:0px;">
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
            <a href="{{ route($routeName, $activity->id) }}" style="padding:10px 12px;background:{{ $isActive ? '#1e5f83' : ($isComplete ? '#154f6eff' : 'transparent') }};border-left:0px solid {{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#ccc') }};border-radius:0px;text-decoration:none;font-size:13px;color:{{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#ffffff') }};font-weight:{{ $isActive ? '600' : '500' }};display:flex;justify-content:space-between;align-items:center;border-bottom: 1px solid #477993;">
                <span>Step {{ $stepNum }}: {{ $stepData['name'] }}</span>
                <span class="steps-number" style="font-size:12px;background: {{ $isActive ? '#ffffff' : ($isComplete ? '#24a745' : '#557f97') }}; color:{{ $isActive ? '#557f97' : ($isComplete ? '#fff' : '#fff') }}; border-radius: 30px; width: 20px; height: 20px; align-items: center; display: flex; justify-content: center; font-weight: bold;">{{ $isComplete ? '✓' : $stepNum }}</span>
            </a>
        @endforeach
    </div>
</div>
