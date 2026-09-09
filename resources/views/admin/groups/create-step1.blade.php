@extends('layouts.admin')

@php $sidebar = 'admin.groups._steps_sidebar'; $currentStep = 1; @endphp

@section('content')
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="group-plan-wrap">
                <div class="group-plan-header-box">
                    <h2>Add New Group Plan</h2>
                    <p>Start by providing your group plan's basic information</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger mt-3 mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
                @endif

                <div class="group-plan-card">
                    <form id="group-step1-form" method="POST" action="{{ (isset($group) && $group->exists) ? route('admin.groups.update', $group->id) : route('admin.groups.store') }}">
                        @csrf

                        <div class="group-plan-step-label">Step 1: Group Plan Creation</div>

                        <div class="form-group-row">
                            <label for="group_name">Group Plan Name <span class="required-mark">*</span></label>
                            <input id="group_name" type="text" name="name" class="form-control" placeholder="e.g., Summer Camp 2026 / Verde Explorers Group" value="{{ old('name', $group->name ?? '') }}" required>
                        </div>

                        <div class="form-group-row">
                            <label>Group Type <span class="required-mark">*</span></label>
                            @php $selectedGroupType = old('group_type', $group->group_type ?? 'Open Group'); @endphp
                            <div class="group-type-toggle-row">
                                <label class="group-type-option {{ ($selectedGroupType == 'Open Group') ? 'selected' : '' }}">
                                    <span class="option-box">
                                        <input type="radio" name="group_type" value="Open Group" {{ ($selectedGroupType == 'Open Group') ? 'checked' : '' }} required>
                                        <span class="option-title">Open Group</span>
                                    </span>
                                    <ul>
                                        <li>Created and managed directly by MPO.</li>
                                        <li>MPO sets up and runs the group</li>
                                        <li>Any traveller account holder can book in</li>
                                        <li>Supports multiple invoices and payment processes</li>
                                    </ul>
                                </label>

                                <label class="group-type-option {{ ($selectedGroupType == 'Closed Group') ? 'selected' : '' }}">
                                    <span class="option-box">
                                        <input type="radio" name="group_type" value="Closed Group" {{ ($selectedGroupType == 'Closed Group') ? 'checked' : '' }} required>
                                        <span class="option-title">Closed Group</span>
                                    </span>
                                    <ul>
                                        <li>Private group requested by an Agent or Organization.</li>
                                        <li>Agent/Org requests MPO to setup the group</li>
                                        <li>Account holder manages bookings &amp; adds guests</li>
                                        <li>Single invoice &amp; bank transfer payment only</li>
                                    </ul>
                                </label>
                            </div>
                        </div>

                        <div class="form-group-row closed-group-client-field {{ ($selectedGroupType == 'Closed Group') ? 'd-block' : 'd-none' }}" id="closed-group-client-wrapper">
                            <label for="closed_group_client">Closed Group Client / Organization <span class="required-mark">*</span></label>
                            <input id="closed_group_client" type="text" name="closed_group_client" class="form-control" placeholder="e.g., ABC Travels / Blue Ocean Resorts" value="{{ old('closed_group_client', $group->closed_group_client ?? '') }}">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-group-row compact">
                                    <label for="no_of_days">No. of Days <span class="required-mark">*</span></label>
                                    <input id="no_of_days" type="number" name="no_of_days" class="form-control" placeholder="e.g., 4" value="{{ old('no_of_days', $group->no_of_days ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-row compact">
                                    <label for="no_of_nights">No. of Nights <span class="required-mark">*</span></label>
                                    <input id="no_of_nights" type="number" name="no_of_nights" class="form-control" placeholder="e.g., 3" value="{{ old('no_of_nights', $group->no_of_nights ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-row compact">
                                    <label for="booking_cutoff_days">Booking Cutoff (days) <span class="required-mark">*</span></label>
                                    <input id="booking_cutoff_days" type="number" name="booking_cutoff_days" class="form-control" placeholder="e.g., 15" value="{{ old('booking_cutoff_days', $group->booking_cutoff_days ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="form-group-row compact">
                                    <label for="available_from">Group Start Date <span class="required-mark">*</span></label>
                                    <input id="available_from" type="date" name="available_from" class="form-control" value="{{ old('available_from', optional($group->available_from)->toDateString() ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-row compact">
                                    <label for="available_to">Group End Date <span class="required-mark">*</span></label>
                                    <input id="available_to" type="date" name="available_to" class="form-control" value="{{ old('available_to', optional($group->available_to)->toDateString() ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="form-group-row compact">
                                    <label for="minimum_pax">Minimum Pax <span class="required-mark">*</span></label>
                                    <input id="minimum_pax" type="number" name="minimum_pax" class="form-control" placeholder="e.g., 1" value="{{ old('minimum_pax', $group->minimum_pax ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-row compact">
                                    <label for="maximum_pax">Maximum Pax <span class="required-mark">*</span></label>
                                    <input id="maximum_pax" type="number" name="maximum_pax" class="form-control" placeholder="e.g., 10" value="{{ old('maximum_pax', $group->maximum_pax ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="group-plan-actions">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light">Back</a>
                            <button type="submit" class="btn btn-primary next-prefix">Next: Add Group</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            const groupTypeInputs = document.querySelectorAll('input[name="group_type"]');
            const closedGroupClientWrapper = document.getElementById('closed-group-client-wrapper');
            const closedGroupClientInput = document.getElementById('closed_group_client');

            function toggleClosedGroupField() {
                const selected = document.querySelector('input[name="group_type"]:checked');
                const isClosed = selected && selected.value === 'Closed Group';

                if (closedGroupClientWrapper) {
                    closedGroupClientWrapper.classList.toggle('d-none', !isClosed);
                    closedGroupClientWrapper.classList.toggle('d-block', isClosed);
                }

                if (closedGroupClientInput) {
                    closedGroupClientInput.required = isClosed;
                    if (!isClosed) {
                        closedGroupClientInput.value = '';
                    }
                }
            }

            groupTypeInputs.forEach(function (radio) {
                radio.addEventListener('change', toggleClosedGroupField);
            });

            if (!document.querySelector('input[name="group_type"]:checked')) {
                const defaultOpen = document.querySelector('input[name="group_type"][value="Open Group"]');
                if (defaultOpen) {
                    defaultOpen.checked = true;
                }
            }

            toggleClosedGroupField();
        });
    </script>

    @push('styles')
<style>
    .group-plan-wrap {
        background: #f1f1f1;
        border-radius: 14px;
        padding: 18px 18px 22px;
        border: 1px solid #d9d9d9;
    }

    .group-plan-header-box {
        background: #ffffff;
        border: 1px solid #dfe3e8;
        border-radius: 10px 10px 0 0;
        padding: 18px 20px 16px;
        margin-bottom: 0;
    }

    .group-plan-header-box h2 {
        margin: 0;
        font-size: 40px;
        line-height: 1.2;
        font-weight: 800;
        color: #1f1f1f;
    }

    .group-plan-header-box p {
        margin: 8px 0 0;
        font-size: 16px;
        color: #5f6368;
    }

    .group-plan-card {
        background: #ffffff;
        border: 1px solid #dfe3e8;
        border-top: none;
        border-radius: 0 0 12px 12px;
        padding: 0 18px 18px;
    }

    .group-plan-step-label {
        background: linear-gradient(90deg, #0d8a90 0%, #0e9ca0 100%);
        color: #fff;
        font-weight: 700;
        font-size: 20px;
        border-radius: 6px;
        padding: 16px 20px;
        margin: 18px 0 20px;
        display: block;
    }

    .form-group-row {
        margin-bottom: 18px;
    }

    .form-group-row.compact {
        margin-bottom: 10px;
    }

    .form-group-row label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2d2d2d;
        font-size: 15px;
    }

    .required-mark {
        color: #e52d2d;
    }

    .form-control {
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        height: 42px;
        background: #fff;
        color: #2f2f2f;
        padding: 10px 12px;
        font-size: 14px;
    }

    .form-control::placeholder {
        color: #9aa0a6;
    }

    .group-type-toggle-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-top: 6px;
    }

    .group-type-option {
        display: flex;
        flex-direction: column;
        cursor: pointer;
        border: 2px solid #59acee;
        border-radius: 10px;
        background: #f4fbff;
        padding: 16px 18px 14px;
        min-height: 180px;
        transition: all 0.2s ease;
    }

    .group-type-option.selected {
        background: #ebf8ff;
        border-color: #1d7ae0;
        box-shadow: inset 0 0 0 1px rgba(29, 122, 224, 0.15);
    }

    .option-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 2px solid #1d7ae0;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        min-width: 180px;
        width: fit-content;
        font-weight: 700;
        color: #1d4ed8;
    }

    .group-type-option input[type="radio"] {
        margin: 0;
        width: 18px;
        height: 18px;
        accent-color: #1d7ae0;
        flex-shrink: 0;
    }

    .option-title {
        font-size: 16px;
        line-height: 1.2;
        color: #1d4ed8;
    }

    .group-type-option ul {
        margin: 0;
        padding-left: 18px;
        color: #3d4b59;
        font-size: 14px;
        line-height: 1.5;
    }

    .group-type-option li {
        margin-bottom: 3px;
    }

    .group-plan-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 22px;
    }

    .next-prefix {
        min-width: 180px;
        background: #0d6efd;
        border: none;
        font-weight: 600;
        padding: 10px 18px;
    }

    @media (max-width: 767px) {
        .group-plan-header-box h2 {
            font-size: 30px;
        }

        .group-type-toggle-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
@endsection
