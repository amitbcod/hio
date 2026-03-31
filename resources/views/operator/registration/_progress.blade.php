<!-- @if(!auth('operator_staff')->check())
<div style="width: 400px; margin: 0 auto 24px auto;">
    <div style="font-weight: bold; margin-bottom: 4px;">Overall Completion</div>
    <div class="progress" style="height: 18px; background: #e6e6e6; border-radius: 12px;">
        @php
            // Always count step1 as filled (registration)
            $displayPercent = isset($completionPercent) ? min(100, $completionPercent + (100/9)) : 0;
        @endphp
        <div class="progress-bar" role="progressbar" aria-valuenow="{{ $displayPercent }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $displayPercent }}%; background: #19b5b5; border-radius: 12px; font-weight: bold; color: #222;">
            {{ round($displayPercent) }}%
        </div>
    </div>
    <div style="text-align: right; font-size: 13px; color: #888; margin-top: 2px;">{{ round($displayPercent) }}% / 9</div>
@endif -->


@if(!auth('operator_staff')->check())

@php
    // Always count step1 as filled (registration)
    $displayPercent = isset($completionPercent) ? min(100, $completionPercent + (100/9)) : 0;
@endphp

<div class="col-md-3 align-items-center justify-content-center" style="min-height: 90vh;margin-top: 60px;">
    <div class="box">
        <div class="percent">
            <svg width="150" height="150">
                
                <!-- Background Circle -->
                <circle cx="70" cy="70" r="70"></circle>

                <!-- Progress Circle -->
                <circle cx="70" cy="70" r="70"
                    style="
                        stroke-dasharray: 440;
                        stroke-dashoffset: calc(440 - (440 * {{ $displayPercent }}) / 100);
                        transition: 0.5s;
                    ">
                </circle>

            </svg>

            <div class="num">
                <h2>{{ round($displayPercent) }}<span>%</span></h2>
            </div>
        </div>

        <h2 class="text">Overall Completion</h2>
    </div>
</div>

@endif