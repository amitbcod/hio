<div style="padding:12px 0;">
    @php $steps = [
        ['label' => 'Step 1: Group Creation', 'route' => (isset($group) && $group->exists) ? route('admin.groups.edit', $group->id) : route('admin.groups.create')],
        ['label' => 'Step 2: Add Group', 'route' => (isset($group) && $group->exists) ? route('admin.groups.step2', $group->id) : '#'],
        ['label' => 'Step 3: Allocation', 'route' => (isset($group) && $group->exists) ? route('admin.groups.step3', $group->id) : '#'],
        ['label' => 'Step 4: Pricing & Rate Plan', 'route' => (isset($group) && $group->exists) ? route('admin.groups.step4', $group->id) : '#'],
        ['label' => 'Step 5: Content & CMS', 'route' => (isset($group) && $group->exists) ? route('admin.groups.step5', $group->id) : '#'],
        ['label' => 'Step 6: Day-wise Itinerary', 'route' => (isset($group) && $group->exists) ? route('admin.groups.step6', $group->id) : '#'],
        ['label' => 'Step 7: Payment & Policies', 'route' => (isset($group) && $group->exists) ? route('admin.groups.step7', $group->id) : '#'],
    ]; @endphp

    <div>
        <h6 style="padding: 12px;font-weight:700; color: #54420b;">Group Steps</h6>
        <div style="display:flex;flex-direction:column;gap:0px;margin-top:8px;">
            @foreach($steps as $index => $s)
                @php $stepNum = $index + 1; $active = (isset($currentStep) && $currentStep === $stepNum); @endphp
                <a href="{{ $s['route'] }}" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:0;text-decoration:none;font-size: 15px;background:{{ $active ? '#917318' : '#c6ac50' }};border-bottom:1px solid #a15b5ba6;color:#fff;font-weight: 600;">
                    <span style="flex:1">{{ $s['label'] }}</span>
                    <span style="background:{{ $active ? '#c6ac50' : '#917318' }};color:#fff;border-radius:50%;width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;">{{ $stepNum }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
