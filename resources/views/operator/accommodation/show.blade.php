@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9" style="margin-top: 30px;">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07);">
                    
                    {{-- Header with Property Info --}}
                    <div style="background: linear-gradient(135deg, #19b5b5, #139999); color: #fff; padding: 32px; border-radius: 16px 16px 0 0;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                            <div>
                                <h2 style="font-weight: bold; margin-bottom: 8px;">{{ $accommodation->property_name }}</h2>
                                <p style="margin-bottom: 0; opacity: 0.9;">
                                    <strong>{{ $accommodation->property_type }}</strong> · {{ $accommodation->city }}, {{ $accommodation->country }}
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 4px; margin-bottom: 8px;">
                                    <p style="margin-bottom: 4px; font-size: 12px; opacity: 0.9;">Property ID</p>
                                    <p style="margin-bottom: 0; font-weight: bold; font-size: 14px;">{{ $accommodation->accommodation_id }}</p>
                                </div>
                                <span class="badge" style="background: rgba(255,255,255,0.3); color: #fff; font-weight: 600;">{{ $accommodation->status }}</span>
                            </div>
                        </div>
                        <p style="margin-bottom: 0; font-size: 14px; opacity: 0.9;">{{ $accommodation->address }}, {{ $accommodation->postal_code }}</p>
                    </div>

                    {{-- Alerts --}}
                    <div style="padding: 32px;">
                        @if(session('success'))
                            <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
                        @endif

                        {{-- Overall Progress --}}
                        <div style="background: #f8f8f8; padding: 20px; border-radius: 8px; margin-bottom: 32px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h5 style="font-weight: 600; margin-bottom: 0;">Property Completion Progress</h5>
                                <span style="font-weight: bold; color: #19b5b5; font-size: 18px;">{{ $accommodation->getCompletionPercentage() }}%</span>
                            </div>
                            <div style="height: 10px; background: #e0e0e0; border-radius: 5px; overflow: hidden;">
                                <div style="height: 100%; background: linear-gradient(90deg, #19b5b5, #139999); width: {{ $accommodation->getCompletionPercentage() }}%; transition: width 0.3s;"></div>
                            </div>
                        </div>

                        {{-- Setup Steps --}}
                        <div style="margin-bottom: 32px;">
                            <h5 style="font-weight: 600; margin-bottom: 20px;">Setup Steps</h5>
                            <div class="row">
                                {{-- Step 1: Basics --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step1_basics ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step1_basics ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step1_basics ? '#28a745' : '#999' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step1_basics ? '✓' : '1' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 1: Accommodation Basics</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Property name, type, address, contacts</p>
                                                @if($accommodation->step1_basics)
                                                    <p style="margin-bottom: 0; margin-top: 8px; font-size: 11px; color: #28a745; font-weight: 600;">✓ Completed</p>
                                                @endif
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step1.edit', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    Edit
                                                </a>
                                            </div>
                                        @else
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step1.edit', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    Complete Step
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 2: Reservation & Communication --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step2_legal ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step2_legal ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step2_legal ? '#28a745' : ($accommodation->step1_basics ? '#999' : '#ddd') }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step2_legal ? '✓' : '2' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 2: Reservation & Communication</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Contact information for bookings and management</p>
                                                @if($accommodation->step2_legal)
                                                    <p style="margin-bottom: 0; margin-top: 8px; font-size: 11px; color: #28a745; font-weight: 600;">✓ Completed</p>
                                                @elseif(!$accommodation->step1_basics)
                                                    <p style="margin-bottom: 0; margin-top: 8px; font-size: 11px; color: #999; font-weight: 600;">Complete Step 1 first</p>
                                                @endif
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step2.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step2_legal ? 'Edit' : 'Complete Step' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 3: Photos & Media --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step3_media ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step3_media ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step3_media ? '#28a745' : ($accommodation->step2_legal ? '#999' : '#ddd') }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step3_media ? '✓' : '3' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 3: Photos & Media</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Upload hero, gallery and room images, logo and optional video.</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics && $accommodation->step2_legal)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step3.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step3_media ? 'Edit' : 'Complete Step' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 4: Compliance & Legal --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step7_compliance ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step7_compliance ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step7_compliance ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step7_compliance ? '✓' : '4' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 4: Compliance & Legal</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Tourism permits, insurance, fire safety and legal documents.</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics && $accommodation->step2_legal)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step4.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step7_compliance ? 'Edit' : 'Complete Step' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 5: Accounting & Transaction --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step5_rates ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step5_rates ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step5_rates ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step5_rates ? '✓' : '5' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 5: Accounting & Transaction</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Bank details, VAT, taxes, commission and currency settings</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step5.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step5_rates ? 'Edit' : 'Complete Step' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 6: Policies & Rules --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step6_policies ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step6_policies ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step6_policies ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step6_policies ? '✓' : '6' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 6: Policies & Rules</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Check-in/out, cancellation, deposits, house rules</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step6.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step6_policies ? 'Edit' : 'Complete Step' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 7: Rooms & Units --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step4_rooms ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step4_rooms ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step4_rooms ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step4_rooms ? '✓' : '7' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 7: Rooms & Units</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Define room types, capacities, amenities</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step7.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step4_rooms ? 'Edit' : 'Complete Step' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 8: Rate Plans --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step8_rates ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step8_rates ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step8_rates ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step8_rates ? '✓' : '8' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 8: Rate Plans</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Define meal plans and pricing</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics && $accommodation->step4_rooms)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step8.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step8_rates ? 'Edit' : 'Set Plans' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 9: Season and Pricing --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step9_pricing ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step9_pricing ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step9_pricing ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step9_pricing ? '✓' : '9' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 9: Season and Pricing</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Set seasonal pricing for different periods</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics && $accommodation->step4_rooms && $accommodation->step8_rates)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step9.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step9_pricing ? 'Edit' : 'Set Pricing' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 10: Inventory & Allotment --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step10_inventory_allotment ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step10_inventory_allotment ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step10_inventory_allotment ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step10_inventory_allotment ? '✓' : '10' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 10: Inventory & Allotment</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Manage room availability and booking restrictions</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics && $accommodation->step4_rooms && $accommodation->step9_pricing)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step10.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step10_inventory_allotment ? 'Edit' : 'Manage Inventory' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 11: Promotions & Offers --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step11_promotions_offers ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step11_promotions_offers ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step11_promotions_offers ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step11_promotions_offers ? '✓' : '11' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 11: Promotions & Offers</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Create and manage special promotions</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics && $accommodation->step4_rooms && $accommodation->step9_pricing)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step11.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step11_promotions_offers ? 'Edit' : 'Add Promotions' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 12: SEO & Social --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step12_review ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step12_review ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step12_review ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step12_review ? '✓' : '12' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 12: SEO & Social</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Meta tags, keywords and OpenGraph for social sharing</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step12.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #19b5b5; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step12_review ? 'Edit' : 'Configure SEO' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Step 13: Publish --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border: 2px solid {{ $accommodation->step13_publish ? '#28a745' : '#e0e0e0' }}; border-radius: 8px; padding: 16px; background: {{ $accommodation->step13_publish ? '#f1f8f7' : '#fafafa' }};">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $accommodation->step13_publish ? '#28a745' : '#ddd' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                                {{ $accommodation->step13_publish ? '✓' : '13' }}
                                            </div>
                                            <div style="flex: 1;">
                                                <h6 style="font-weight: 600; margin-bottom: 4px;">Step 13: Review & Publish</h6>
                                                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Review and submit for approval</p>
                                            </div>
                                        </div>
                                        @if($accommodation->step1_basics)
                                            <div style="margin-top: 12px;">
                                                <a href="{{ route('operator.accommodation.step13.show', $accommodation->id) }}" class="btn" style="display: inline-block; background: #28a745; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none;">
                                                    {{ $accommodation->step13_publish ? 'View Status' : 'Review & Submit' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Info --}}
                        <div class="row" style="margin-bottom: 32px;">
                            <div class="col-md-6 mb-3">
                                <div style="background: #f9f9f9; padding: 16px; border-radius: 8px;">
                                    <h6 style="font-weight: 600; margin-bottom: 12px;">Property Details</h6>
                                    <div style="font-size: 12px; line-height: 1.8;">
                                        <p style="margin-bottom: 8px;"><strong>Type:</strong> {{ $accommodation->property_type }}</p>
                                        <p style="margin-bottom: 8px;"><strong>Location:</strong> {{ $accommodation->city }}, {{ $accommodation->country }}</p>
                                        <p style="margin-bottom: 0;"><strong>Created:</strong> {{ $accommodation->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div style="background: #f9f9f9; padding: 16px; border-radius: 8px;">
                                    <h6 style="font-weight: 600; margin-bottom: 12px;">Reservation Contact</h6>
                                    <div style="font-size: 12px; line-height: 1.8;">
                                        <p style="margin-bottom: 8px;"><strong>{{ $accommodation->reservation_contact_name }}</strong></p>
                                        <p style="margin-bottom: 8px;">📧 <a href="mailto:{{ $accommodation->reservation_contact_email }}" style="color: #19b5b5; text-decoration: none;">{{ $accommodation->reservation_contact_email }}</a></p>
                                        <p style="margin-bottom: 0;">📱 {{ $accommodation->reservation_contact_phone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div style="display: flex; gap: 12px;">
                            <a href="{{ route('operator.accommodation.index') }}" class="btn" style="background: #f0f0f0; color: #333; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 600;">
                                ← Back to Properties
                            </a>
                            @if($accommodation->getCompletionPercentage() < 100)
                                <!-- <button type="button" class="btn" style="background: #19b5b5; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 600;">
                                    Continue Setup
                                </button> -->
                            @else
                                <button type="button" class="btn" style="background: #28a745; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 600;">
                                    ✓ Ready to Publish
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

 <script>
      function toggleMenu(element) {
         let submenu = element.nextElementSibling;

         element.classList.toggle("active");
         submenu.classList.toggle("hidden");
      }
   </script>
   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }
   </script>

   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }

      document.addEventListener("click", function (e) {
         let sidebar = document.getElementById("sidebar");
         let hamburger = document.querySelector(".hamburger");

         if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove("active");
         }
      });
   </script>