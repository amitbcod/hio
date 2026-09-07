@extends('layouts.admin')

@php $sidebar = 'admin.packages._steps_sidebar'; $currentStep = 2; @endphp

@section('content')
<div class="container mt-5">
    <div style="background:#fff;border-radius:12px;padding:18px;margin-bottom:16px;">
        <h1 style="margin:0;font-weight:700;">Add New Package</h1>
        <p style="color:#666;margin-top:6px;">Build the day-by-day itinerary for this package</p>
    </div>

    <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
        <h5 style="background:#0e8f84;color:#fff;padding:10px;border-radius:6px;display:inline-block;">Step 2: Add Package</h5>

        @php $itinerary = $package->itinerary ?? []; @endphp
        <form method="POST" action="{{ route('admin.packages.step2.store', $package->id) }}">
            @csrf

            {{-- Removed separate search fields; selects are searchable via Select2 --}}

            @foreach($dates as $i => $date)
                <div style="border:1px solid #eee;border-radius:8px;padding:14px;margin-bottom:12px;">
                    <h6 style="margin:0 0 8px 0;font-weight:700;">Day {{ $i + 1 }}</h6>
                    <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Accommodation</label>

                                <div class="mb-2 small text-muted">Show: 
                                    <div class="btn-group ms-2" role="group" aria-label="accommodation-filter-{{ $i }}">
                                        <input type="radio" class="btn-check acc-filter" name="acc_filter_{{ $i }}" id="acc_filter_{{ $i }}_all" autocomplete="off" value="all" checked>
                                        <label class="btn btn-outline-secondary btn-sm" for="acc_filter_{{ $i }}_all">All</label>

                                        <input type="radio" class="btn-check acc-filter" name="acc_filter_{{ $i }}" id="acc_filter_{{ $i }}_having" autocomplete="off" value="having">
                                        <label class="btn btn-outline-secondary btn-sm" for="acc_filter_{{ $i }}_having">Having package price</label>

                                        <input type="radio" class="btn-check acc-filter" name="acc_filter_{{ $i }}" id="acc_filter_{{ $i }}_not" autocomplete="off" value="not">
                                        <label class="btn btn-outline-secondary btn-sm" for="acc_filter_{{ $i }}_not">Not having package price</label>
                                    </div>
                                </div>

                                <select name="itinerary[{{ $i }}][accommodation]" class="form-select accommodation-select" data-day-index="{{ $i }}">
                                    <option value="">-- Select accommodation --</option>
                                    @foreach($availableAccommodations[$i] ?? [] as $accEntry)
                                        @php
                                            $acc = $accEntry['model'];
                                            $hasPkg = !empty($accEntry['has_package']) ? 1 : 0;
                                            $title = $acc->property_name ?? $acc->name ?? optional($acc->business)->name ?? ('Accommodation #' . $acc->id);
                                            $type = $acc->property_type ?? null;
                                            $selectedAcc = $itinerary[$i]['accommodation'] ?? '';
                                        @endphp
                                        <option value="{{ $acc->id }}" data-has-package="{{ $hasPkg }}" {{ $selectedAcc == $acc->id ? 'selected' : '' }}>{{ $title }}@if($type) ({{ $type }}) @endif</option>
                                    @endforeach
                                </select>
                            @if($i === 0)
                                <div style="margin-top:6px;">
                                    <a href="#" id="apply-acc-all">Apply to all days</a>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Activity</label>
                            <div class="mb-2 small text-muted">Show: 
                                <div class="btn-group ms-2" role="group" aria-label="activity-filter-{{ $i }}">
                                    <input type="radio" class="btn-check act-filter" name="act_filter_{{ $i }}" id="act_filter_{{ $i }}_all" autocomplete="off" value="all" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="act_filter_{{ $i }}_all">All</label>

                                    <input type="radio" class="btn-check act-filter" name="act_filter_{{ $i }}" id="act_filter_{{ $i }}_having" autocomplete="off" value="having">
                                    <label class="btn btn-outline-secondary btn-sm" for="act_filter_{{ $i }}_having">Having package price</label>

                                    <input type="radio" class="btn-check act-filter" name="act_filter_{{ $i }}" id="act_filter_{{ $i }}_not" autocomplete="off" value="not">
                                    <label class="btn btn-outline-secondary btn-sm" for="act_filter_{{ $i }}_not">Not having package price</label>
                                </div>
                            </div>
                            <select name="itinerary[{{ $i }}][activity]" class="form-select activity-select" data-day-index="{{ $i }}">
                                <option value="">-- Select activity --</option>
                                @php $selectedAct = $itinerary[$i]['activity'] ?? ''; @endphp
                                @foreach($availableActivities[$i] ?? [] as $actEntry)
                                    @php $act = $actEntry['model']; $hasPkg = !empty($actEntry['has_package']) ? 1 : 0; @endphp
                                    <option value="{{ $act->id }}" data-has-package="{{ $hasPkg }}" {{ $selectedAct == $act->id ? 'selected' : '' }}>{{ $act->activity_name ?? $act->name ?? 'Activity #' . $act->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transport</label>
                            <div class="mb-2 small text-muted">Show: 
                                <div class="btn-group ms-2" role="group" aria-label="transport-filter-{{ $i }}">
                                    <input type="radio" class="btn-check trn-filter" name="trn_filter_{{ $i }}" id="trn_filter_{{ $i }}_all" autocomplete="off" value="all" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="trn_filter_{{ $i }}_all">All</label>

                                    <input type="radio" class="btn-check trn-filter" name="trn_filter_{{ $i }}" id="trn_filter_{{ $i }}_having" autocomplete="off" value="having">
                                    <label class="btn btn-outline-secondary btn-sm" for="trn_filter_{{ $i }}_having">Having package price</label>

                                    <input type="radio" class="btn-check trn-filter" name="trn_filter_{{ $i }}" id="trn_filter_{{ $i }}_not" autocomplete="off" value="not">
                                    <label class="btn btn-outline-secondary btn-sm" for="trn_filter_{{ $i }}_not">Not having package price</label>
                                </div>
                            </div>
                            <select name="itinerary[{{ $i }}][transport]" class="form-select transport-select" data-day-index="{{ $i }}">
                                <option value="">-- Select transport --</option>
                                @php $selectedTrn = $itinerary[$i]['transport'] ?? ''; @endphp
                                @foreach($transports as $tEntry)
                                    @php
                                        $t = $tEntry['model'];
                                        $hasPkg = !empty($tEntry['has_package']) ? 1 : 0;
                                        $reg = $t->registration_number ?? null;
                                        $veh = $t->vehicle_type ?? null;
                                        $label = $reg ? ($reg . ($veh ? ' (' . $veh . ')' : '')) : ($t->name ?? 'Transport #' . $t->id);
                                    @endphp
                                    <option value="{{ $t->id }}" data-has-package="{{ $hasPkg }}" {{ $selectedTrn == $t->id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endforeach

            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:12px;">
                <a href="{{ route('admin.packages.create') }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary next-prefix">Next: Allocation</button>
            </div>
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container--bootstrap5 .select2-selection { height: calc(1.5em + .75rem + 2px); }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // initialize Select2 on all selects to provide searching inside dropdowns
                $('.accommodation-select, .activity-select, .transport-select').select2({
                    theme: 'bootstrap5',
                    width: '100%',
                    placeholder: '-- Select --',
                    allowClear: true,
                });

                // cache original options for each select to enable client-side filtering
                function cacheOptions($select) {
                    const opts = [];
                    $select.find('option').each(function() {
                        opts.push({ value: this.value, text: $(this).text(), has: $(this).data('has-package') == 1 ? 1 : 0, selected: $(this).is(':selected') });
                    });
                    $select.data('origOptions', opts);
                }

                $('.accommodation-select, .activity-select, .transport-select').each(function() {
                    cacheOptions($(this));
                });

                function applyFilter($select, mode) {
                    const orig = $select.data('origOptions') || [];
                    // destroy select2, rebuild options
                    $select.select2('destroy');
                    $select.empty();
                    $select.append($('<option>', { value: '', text: '-- Select --' }));
                    orig.forEach(function(o) {
                        const keep = (mode === 'all') || (mode === 'having' && o.has === 1) || (mode === 'not' && o.has === 0);
                        if (keep) {
                            const opt = $('<option>', { value: o.value, text: o.text }).attr('data-has-package', o.has);
                            if (o.selected) opt.prop('selected', true);
                            $select.append(opt);
                        }
                    });
                    $select.select2({ theme: 'bootstrap5', width: '100%', placeholder: '-- Select --', allowClear: true });
                }

                // handle radio change events per day
                $(document).on('change', '.acc-filter', function() {
                    const name = $(this).attr('name');
                    const day = name.replace('acc_filter_', '');
                    const mode = $(this).val();
                    const $sel = $('.accommodation-select[data-day-index="' + day + '"]');
                    applyFilter($sel, mode);
                });

                $(document).on('change', '.act-filter', function() {
                    const name = $(this).attr('name');
                    const day = name.replace('act_filter_', '');
                    const mode = $(this).val();
                    const $sel = $('.activity-select[data-day-index="' + day + '"]');
                    applyFilter($sel, mode);
                });

                $(document).on('change', '.trn-filter', function() {
                    const name = $(this).attr('name');
                    const day = name.replace('trn_filter_', '');
                    const mode = $(this).val();
                    const $sel = $('.transport-select[data-day-index="' + day + '"]');
                    applyFilter($sel, mode);
                });

                // Apply accommodation from first day to all remaining days
                const applyLink = document.getElementById('apply-acc-all');
                if (applyLink) {
                    applyLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        const selects = $('.accommodation-select');
                        if (selects.length <= 1) return;
                        const firstVal = $(selects[0]).val();
                        if (!firstVal) {
                            alert('Please select an accommodation on Day 1 first');
                            return;
                        }
                        selects.each(function(idx, el) {
                            if (idx === 0) return;
                            $(el).val(firstVal).trigger('change');
                        });
                    });
                }
            });
        </script>
    @endpush
</div>
@endsection
