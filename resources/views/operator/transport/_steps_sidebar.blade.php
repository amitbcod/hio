<!-- Transport Steps Sidebar -->
<div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);position:sticky;top:92px;max-height:calc(100vh - 92px);overflow:auto;box-sizing:border-box;min-width:200px;width:auto;">
    <h6 style="font-weight:700;margin:0 0 16px 0;font-size:14px;color:#333;">Transport Steps</h6>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @php
            $steps = [
                1 => ['name' => 'Basics', 'field' => 'step1_basics', 'route' => 'operator.transport.basic-details'],
                2 => ['name' => 'Routes & Pricing', 'field' => 'step2_routes_pricing', 'route' => 'operator.transport.step2.show'],
                3 => ['name' => 'Media', 'field' => 'step3_media', 'route' => 'operator.transport.step3.show'],
                4 => ['name' => 'Compliance', 'field' => 'step4_compliance', 'route' => 'operator.transport.step4.show'],
                5 => ['name' => 'Promotions & Offers', 'field' => 'step5_promotions_offers', 'route' => 'operator.transport.step5.show'],
                6 => ['name' => 'Service Description', 'field' => 'step6_service_description', 'route' => 'operator.transport.step6-service-description.show'],
                7 => ['name' => 'SEO & Social', 'field' => 'step6_seo_social', 'route' => 'operator.transport.step6.show'],
                8 => ['name' => 'Publish', 'field' => 'step7_publish', 'route' => 'operator.transport.step7.show'],
            ];
            $currentStep = $currentStep ?? null;
        @endphp
        @foreach($steps as $stepNum => $stepData)
            @php
                $isComplete = isset($transport) && ($transport->{$stepData['field']} ?? false);
                $isActive = ($currentStep === $stepNum);
                $routeName = $stepData['route'];
                if ($stepNum === 1) {
                    if (isset($transport) && $transport->id) {
                        // When editing an existing transport, Step 1 should open the edit page for that transport
                        $routeUrl = route('operator.transport.edit', $transport->id);
                    } else {
                        // Fallback to the basic details wizard when no transport is present
                        $routeUrl = route($routeName);
                    }
                } elseif (isset($transport) && $transport->id) {
                    $routeUrl = route($routeName, $transport->id);
                } else {
                    $routeUrl = '#';
                }
            @endphp
            <a href="{{ $routeUrl }}" style="padding:10px 12px;background:{{ $isActive ? '#e3f2fd' : ($isComplete ? '#e8f5e9' : '#f5f5f5') }};border-left:4px solid {{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#ccc') }};border-radius:4px;text-decoration:none;font-size:13px;color:{{ $isActive ? '#2196f3' : ($isComplete ? '#28a745' : '#666') }};font-weight:{{ $isActive ? '600' : '500' }};display:flex;justify-content:space-between;align-items:center;">
                <span>Step {{ $stepNum }}: {{ $stepData['name'] }}</span>
                <span style="font-size:12px;">{{ $isComplete ? '✓' : $stepNum }}</span>
            </a>
        @endforeach
    </div>
</div>
