<div style="padding:12px;">
    @php $steps = [
        ['label' => 'Step 1: Package Creation', 'route' => (isset($package) && $package->exists) ? route('admin.packages.edit', $package->id) : route('admin.packages.create')],
        ['label' => 'Step 2: Add Package', 'route' => (isset($package) && $package->exists) ? route('admin.packages.step2', $package->id) : '#'],
        ['label' => 'Step 3: Allocation', 'route' => (isset($package) && $package->exists) ? route('admin.packages.step3', $package->id) : '#'],
        ['label' => 'Step 4: Pricing & Rate Plan', 'route' => (isset($package) && $package->exists) ? route('admin.packages.step4', $package->id) : '#'],
        ['label' => 'Step 5: Content & CMS', 'route' => (isset($package) && $package->exists) ? route('admin.packages.step5', $package->id) : '#'],
        ['label' => 'Step 6: Day-wise Itinerary', 'route' => (isset($package) && $package->exists) ? route('admin.packages.step6', $package->id) : '#'],
        ['label' => 'Step 7: Payment & Policies', 'route' => (isset($package) && $package->exists) ? route('admin.packages.step7', $package->id) : '#'],
    ]; @endphp

    <div style="background:#fff;border-radius:8px;padding:12px;">
        <h6 style="margin:0 0 8px 0;font-weight:700;">Package Steps</h6>
        <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
            @foreach($steps as $index => $s)
                @php $stepNum = $index + 1; $active = (isset($currentStep) && $currentStep === $stepNum); @endphp
                <a href="{{ $s['route'] }}" style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:6px;text-decoration:none;background:{{ $active ? '#e6f2ef' : '#f8f8f8' }};border:1px solid {{ $active ? '#19b5b5' : '#eee' }};color:#333;">
                    <span style="flex:1">{{ $s['label'] }}</span>
                    <span style="background:{{ $active ? '#19b5b5' : '#e9ecef' }};color:#fff;border-radius:50%;width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;">{{ $stepNum }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
