<div style="background: #19b5b5; min-height: 100vh; width: 240px; padding: 0; color: #fff; border-radius: 0 16px 16px 0;">
    <div style="padding: 24px 0 8px 24px; font-weight: bold; letter-spacing: 1px; font-size: 18px;">PROFILE CREATION</div>
    <ul style="list-style: none; padding: 0; margin: 0;">
        {{-- DEBUG: Show progress values for troubleshooting --}}
        {{-- Debug progress removed --}}
        @php 
            // Map actual route step numbers to display step numbers
            // Since we removed step 3 (Legal Compliance), step 4+ are now displayed as step 3+
            $stepNumberMap = [
                1 => 1,  // Registration stays 1
                2 => 2,  // Profile stays 2
                4 => 3,  // System Processes (was 4, now 3)
                5 => 4,  // Collaboration (was 5, now 4)
                6 => 5,  // Users (was 6, now 5)
                7 => 6,  // Accounting (was 7, now 6)
                8 => 7,  // Service Operations (was 8, now 7)
                9 => 8,  // Status Review (was 9, now 8)
            ];
            
            // Convert actual $currentStep to display step number
            $displayCurrentStep = isset($stepNumberMap[$currentStep]) ? $stepNumberMap[$currentStep] : $currentStep;
            
            $steps = [
                1 => ['label' => 'Registration', 'route' => null, 'progress' => 'step1_password'],
                2 => ['label' => 'Profile', 'route' => 'operator.register.step2', 'progress' => 'step2_profile'],
                3 => ['label' => 'System Processes', 'route' => 'operator.register.step4', 'progress' => 'step4_system_process'],
                4 => ['label' => 'Collaboration Agreement', 'route' => 'operator.register.step5', 'progress' => 'step5_collaboration'],
                5 => ['label' => 'Users & Staff', 'route' => 'operator.register.step6', 'progress' => 'step6_users'],
                6 => ['label' => 'Accounting & Payouts', 'route' => 'operator.register.step7', 'progress' => 'step7_accounting'],
                7 => ['label' => 'Service Operations', 'route' => 'operator.register.step8', 'progress' => 'step8_operations'],
                8 => ['label' => 'Status Review', 'route' => 'operator.register.step9', 'progress' => 'step9_review'],
            ];
            $progress = isset($progress) ? $progress : (\App\Models\OperatorRegistrationProgress::where('operator_id', auth()->user()->operator_id ?? null)->first());
        @endphp
        @foreach($steps as $step => $info)
            <li style="margin-bottom: 4px;">
                @php
                    // Check if this step is accessible
                    // Step 1 and 2 are always accessible
                    // Step N is accessible if Step N-1 is completed
                    $isCompleted = $progress && $progress->{$info['progress']} ? true : false;
                    $isPreviousCompleted = true;
                    
                    if ($step > 2) {
                        // Map steps to their progress column names
                        $progressMap = [
                            2 => 'step2_profile',
                            3 => 'step4_system_process',
                            4 => 'step5_collaboration',
                            5 => 'step6_users',
                            6 => 'step7_accounting',
                            7 => 'step8_operations',
                            8 => 'step9_review'
                        ];
                        $previousStep = $step - 1;
                        $previousStepKey = $progressMap[$previousStep] ?? null;
                        $isPreviousCompleted = $progress && $previousStepKey && $progress->{$previousStepKey} ? true : false;
                    }
                    
                    $isAccessible = $step <= 2 || $isPreviousCompleted;
                @endphp
                @if($info['route'])
                    @if($isAccessible)
                        <a href="{{ route($info['route']) }}" style="display: flex; align-items: center; padding: 12px 24px; color: #fff; text-decoration: none; background: {{ $displayCurrentStep == $step ? '#0e7c7b' : 'transparent' }}; border-radius: 0 16px 16px 0; font-weight: {{ $displayCurrentStep == $step ? 'bold' : 'normal' }}; cursor: pointer;">
                            <span style="display: inline-block; width: 28px; height: 28px; background: #fff; color: #19b5b5; border-radius: 50%; text-align: center; line-height: 28px; font-weight: bold; margin-right: 12px;">{{ $step }}</span>
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
</div>