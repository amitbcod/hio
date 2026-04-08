<div>
    @php
        $operator = auth()->user();
        $business = null;
        
        // Load business if operator is linked
        if (!empty($operator->business_id)) {
            $business = \App\Models\Business::find($operator->business_id);
        }
        
        // Check if business is approved/active
        $isBusinessApproved = $business && $business->status === 'active';
    @endphp

    @if($isBusinessApproved)
        {{-- Show Management Sidebar for Approved Business --}}
        @include('operator.management._sidebar')

        {{-- Registration Steps as Collapsible Section --}}
        <div style="background: #4c938d;color: #ffffff;margin-top: 0;padding: 12px 0;">
            <div style="padding: 12px 20px; font-weight: bold; letter-spacing: 1px; font-size: 14px; cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between;" onclick="document.getElementById('registrationStepsCollapse').classList.toggle('hidden');">
                <span>PROFILE SETUP</span>
                <span id="registrationToggleIcon" style="font-size: 16px; transition: transform 0.3s;">▼</span>
            </div>
            <ul id="registrationStepsCollapse" class="hidden" style="list-style: none; padding: 0; margin: 0;">
                @php 
                    $isHeadOfDepartment = auth('operator_staff')->check();
                    
                    $stepNumberMap = [
                        1 => 1,
                        2 => 2,
                        5 => 3,
                        6 => 4,
                        4 => 5,
                        7 => 6,
                        8 => 7,
                        9 => 8,
                    ];
                    
                    $displayCurrentStep = isset($currentStep) ? (isset($stepNumberMap[$currentStep]) ? $stepNumberMap[$currentStep] : $currentStep) : null;
                    
                    if ($isHeadOfDepartment) {
                        $steps = [
                            5 => ['label' => 'Users & Staff', 'route' => 'operator.register.step6', 'progress' => 'step6_users'],
                        ];
                    } else {
                        $steps = [
                            1 => ['label' => 'Registration', 'route' => null, 'progress' => 'step1_password'],
                            2 => ['label' => 'Profile', 'route' => 'operator.register.step2', 'progress' => 'step2_profile'],
                            3 => ['label' => 'Collaboration Agreement', 'route' => 'operator.register.step5', 'progress' => 'step5_collaboration'],
                            4 => ['label' => 'Users & Staff', 'route' => 'operator.register.step6', 'progress' => 'step6_users'],
                            5 => ['label' => 'System Processes', 'route' => 'operator.register.step4', 'progress' => 'step4_system_process'],
                            6 => ['label' => 'Accounting & Payouts', 'route' => 'operator.register.step7', 'progress' => 'step7_accounting'],
                            7 => ['label' => 'Service Operations', 'route' => 'operator.register.step8', 'progress' => 'step8_operations'],
                            8 => ['label' => 'Status Review', 'route' => 'operator.register.step9', 'progress' => 'step9_review'],
                        ];
                    }
                    
                    $progress = isset($progress) ? $progress : (
                        !empty(auth()->user()->business_id)
                            ? \App\Models\OperatorRegistrationProgress::where('business_id', auth()->user()->business_id)->first()
                            : \App\Models\OperatorRegistrationProgress::where('operator_id', auth()->user()->operator_id ?? null)->first()
                    );
                @endphp
                @foreach($steps as $step => $info)
                    <li style="margin-bottom: 2px;">
                        @php
                            $isCompleted = $progress && $progress->{$info['progress']} ? true : false;
                            $isPreviousCompleted = true;
                            
                            if ($step > 2 && !$isHeadOfDepartment) {
                                $progressMap = [
                                    2 => 'step2_profile',
                                    3 => 'step5_collaboration',
                                    4 => 'step6_users',
                                    5 => 'step4_system_process',
                                    6 => 'step7_accounting',
                                    7 => 'step8_operations',
                                    8 => 'step9_review'
                                ];
                                $previousStep = $step - 1;
                                $previousStepKey = $progressMap[$previousStep] ?? null;
                                $isPreviousCompleted = $progress && $previousStepKey && $progress->{$previousStepKey} ? true : false;
                            } elseif ($isHeadOfDepartment) {
                                $isPreviousCompleted = true;
                            }
                            
                            $isAccessible = $step <= 2 || $isPreviousCompleted || $isHeadOfDepartment;
                        @endphp
                        @if($info['route'])
                            @if($isAccessible)
                                <a href="{{ route($info['route']) }}" style="display: flex; align-items: center; padding: 8px 24px; color: #ffffff; text-decoration: none; background: transparent; border-radius: 0; font-weight: normal; cursor: pointer; font-size: 12px;">
                                    @if(!$isHeadOfDepartment)
                                    <span style="display: inline-block; width: 22px; height: 22px; background: #19b5b5; color: #fff; border-radius: 50%; text-align: center; line-height: 22px; font-weight: bold; margin-right: 8px; font-size: 11px;">{{ $step }}</span>
                                    @endif
                                    {{ $info['label'] }}
                                </a>
                            @else
                                <span style="display: flex; align-items: center; padding: 8px 24px; color: rgba(51,51,51,0.5); background: transparent; border-radius: 0; font-weight: normal; cursor: not-allowed; font-size: 12px;">
                                    <span style="display: inline-block; width: 22px; height: 22px; background: rgba(25,181,181,0.3); color: #19b5b5; border-radius: 50%; text-align: center; line-height: 22px; font-weight: bold; margin-right: 8px; font-size: 11px;">{{ $step }}</span>
                                    {{ $info['label'] }}
                                </span>
                            @endif
                        @else
                            <span style="display: flex; align-items: center; padding: 8px 24px; color: #ffffff; background: transparent; border-radius: 0; font-weight: bold; font-size: 12px;">
                                <span style="display: inline-block; width: 22px; height: 22px; background: #19b5b5; color: #fff; border-radius: 50%; text-align: center; line-height: 22px; font-weight: bold; margin-right: 8px; font-size: 11px;">{{ $step }}</span>
                                {{ $info['label'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <script>
            document.getElementById('registrationStepsCollapse').addEventListener('click', function(e) {
                if (e.target.closest('#registrationToggleIcon')?.parentElement) return;
            });
            document.querySelector('[onclick*="registrationStepsCollapse"]').addEventListener('click', function() {
                const icon = document.getElementById('registrationToggleIcon');
                icon.style.transform = document.getElementById('registrationStepsCollapse').classList.contains('hidden') 
                    ? 'rotate(0deg)' 
                    : 'rotate(180deg)';
            });
        </script>
        <style>
            .hidden { display: none !important; }
        </style>

    @else
        {{-- Show Registration Sidebar for Non-Approved Business --}}
        <div style="padding: 24px 0 8px 24px; font-weight: bold; letter-spacing: 1px; font-size: 18px;">PROFILE CREATION</div>
        <ul style="list-style: none; padding: 0; margin: 0;">
            @php 
                $isHeadOfDepartment = auth('operator_staff')->check();
                
                $stepNumberMap = [
                    1 => 1,
                    2 => 2,
                    5 => 3,
                    6 => 4,
                    4 => 5,
                    7 => 6,
                    8 => 7,
                    9 => 8,
                ];
                
                $displayCurrentStep = isset($stepNumberMap[$currentStep]) ? $stepNumberMap[$currentStep] : $currentStep;
                
                if ($isHeadOfDepartment) {
                    $steps = [
                        5 => ['label' => 'Users & Staff', 'route' => 'operator.register.step6', 'progress' => 'step6_users'],
                    ];
                } else {
                    $steps = [
                        1 => ['label' => 'Registration', 'route' => null, 'progress' => 'step1_password'],
                        2 => ['label' => 'Profile', 'route' => 'operator.register.step2', 'progress' => 'step2_profile'],
                        3 => ['label' => 'Collaboration Agreement', 'route' => 'operator.register.step5', 'progress' => 'step5_collaboration'],
                        4 => ['label' => 'Users & Staff', 'route' => 'operator.register.step6', 'progress' => 'step6_users'],
                        5 => ['label' => 'System Processes', 'route' => 'operator.register.step4', 'progress' => 'step4_system_process'],
                        6 => ['label' => 'Accounting & Payouts', 'route' => 'operator.register.step7', 'progress' => 'step7_accounting'],
                        7 => ['label' => 'Service Operations', 'route' => 'operator.register.step8', 'progress' => 'step8_operations'],
                        8 => ['label' => 'Status Review', 'route' => 'operator.register.step9', 'progress' => 'step9_review'],
                    ];
                }
                
                $progress = isset($progress) ? $progress : (
                    !empty(auth()->user()->business_id)
                        ? \App\Models\OperatorRegistrationProgress::where('business_id', auth()->user()->business_id)->first()
                        : \App\Models\OperatorRegistrationProgress::where('operator_id', auth()->user()->operator_id ?? null)->first()
                );
            @endphp
            @foreach($steps as $step => $info)
                <li style="margin-bottom: 4px;">
                    @php
                        $isCompleted = $progress && $progress->{$info['progress']} ? true : false;
                        $isPreviousCompleted = true;
                        
                        if ($step > 2 && !$isHeadOfDepartment) {
                            $progressMap = [
                                2 => 'step2_profile',
                                3 => 'step5_collaboration',
                                4 => 'step6_users',
                                5 => 'step4_system_process',
                                6 => 'step7_accounting',
                                7 => 'step8_operations',
                                8 => 'step9_review'
                            ];
                            $previousStep = $step - 1;
                            $previousStepKey = $progressMap[$previousStep] ?? null;
                            $isPreviousCompleted = $progress && $previousStepKey && $progress->{$previousStepKey} ? true : false;
                        } elseif ($isHeadOfDepartment) {
                            $isPreviousCompleted = true;
                        }
                        
                        $isAccessible = $step <= 2 || $isPreviousCompleted || $isHeadOfDepartment;
                    @endphp
                    @if($info['route'])
                        @if($isAccessible)
                            <a href="{{ route($info['route']) }}" style="display: flex; align-items: center; padding: 12px 24px; color: #fff; text-decoration: none; background: {{ $displayCurrentStep == $step ? '#0e7c7b' : 'transparent' }}; border-radius: 0 16px 16px 0; font-weight: {{ $displayCurrentStep == $step ? 'bold' : 'normal' }}; cursor: pointer;">
                                @if(!$isHeadOfDepartment)
                                <span style="display: inline-block; width: 28px; height: 28px; background: #fff; color: #19b5b5; border-radius: 50%; text-align: center; line-height: 28px; font-weight: bold; margin-right: 12px;">{{ $step }}</span>
                                @endif
                                {{ $info['label'] }}
                            </a>
                        @else
                            <span style="display: flex; align-items: center; padding: 12px 24px; color: rgba(255,255,255,0.5); background: transparent; border-radius: 0 16px 16px 0; font-weight: normal; cursor: not-allowed;">
                                <span style="display: inline-block; width: 28px; height: 28px; background: rgba(255,255,255,0.5); color: #19b5b5; border-radius: 50%; text-align: center; line-height: 28px; font-weight: bold; margin-right: 12px;">{{ $step }}</span>
                                {{ $info['label'] }} (Locked)
                            </span>
                        @endif
                    @else
                        <span style="display: flex; align-items: center; padding: 12px 24px; color: #fff; background: transparent; border-radius: 0 16px 16px 0; font-weight: bold;">
                            <span style="display: inline-block; width: 28px; height: 28px; background: #fff; color: #19b5b5; border-radius: 50%; text-align: center; line-height: 28px; font-weight: bold; margin-right: 12px;">{{ $step }}</span>
                            {{ $info['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
php artisan migrate  # Run migrations