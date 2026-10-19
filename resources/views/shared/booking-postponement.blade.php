@php
    $postponementPanelId = 'postponement-panel-' . $serviceType . '-' . $booking->getKey();
    $hasPostponementErrors = $errors->has('schedule')
        || $errors->has('new_date')
        || $errors->has('new_time')
        || $errors->has('new_time_slot_id')
        || $errors->has('reason');
    $isPostponementPanelOpen = ($isAdmin && session()->has('success'))
        || (!$isAdmin && session('success') === __('booking_postponement.success'))
        || ($hasPostponementErrors && collect($postponementActions)
            ->contains(fn ($action) => old('expected_schedule') === $action['expected_schedule']));
@endphp

<section class="mb-4">
    <button class="btn btn-outline-primary"
            type="button"
            data-postponement-toggle
            aria-controls="{{ $postponementPanelId }}"
            aria-expanded="{{ $isPostponementPanelOpen ? 'true' : 'false' }}">
        {{ __('booking_postponement.title') }}
    </button>
    <div id="{{ $postponementPanelId }}" class="card mt-3 shadow-sm border-0" data-postponement-panel @if(!$isPostponementPanelOpen) hidden @endif>
        <div class="card-body">
            @if($isAdmin && session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @error('schedule')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

        @foreach($postponementActions as $action)
            @php
                $currentDate = $action['date']
                    ? \Carbon\Carbon::parse($action['date'])->format('M d, Y')
                    : __('booking_postponement.not_available');
                $currentTime = $action['time'] ?: __('booking_postponement.time_not_set');
            @endphp
            <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <strong>{{ $action['label'] }}</strong>
                    <span>
                        {{ __('booking_postponement.current_schedule') }}:
                        {{ $currentDate }}{{ $action['time'] ? ' · ' . $currentTime : '' }}
                        @if($serviceType === 'accommodation' && $action['checkout_date'])
                            — {{ \Carbon\Carbon::parse($action['checkout_date'])->format('M d, Y') }}
                            @if($action['checkout_time'])
                                {{ __('booking_postponement.until') }} {{ $action['checkout_time'] }}
                            @endif
                        @elseif($serviceType === 'activity' && $action['time_slot_label'])
                            · {{ $action['time_slot_label'] }}
                        @endif
                    </span>
                </div>

                @if($action['can_submit'])
                    <form method="POST"
                              action="{{ route($isAdmin ? 'admin.bookings.postpone' : 'operator.bookings.postpone', ['serviceType' => $serviceType, 'booking' => $booking->getKey()]) }}"
                              onsubmit="const button = this.querySelector('button[type=submit]'); if (button) button.disabled = true;">
                            @csrf
                            @if($serviceType === 'transport')
                                <input type="hidden" name="leg" value="{{ $action['leg'] }}">
                            @endif
                            <input type="hidden" name="expected_schedule" value="{{ $action['expected_schedule'] }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="new_date_{{ $serviceType }}_{{ $action['leg'] }}">
                                        {{ $serviceType === 'accommodation' ? __('booking_postponement.new_check_in_date') : __('booking_postponement.new_date') }}
                                    </label>
                                    <input type="date"
                                           class="form-control @error('new_date') is-invalid @enderror"
                                           id="new_date_{{ $serviceType }}_{{ $action['leg'] }}"
                                           name="new_date"
                                           min="{{ now(config('app.timezone'))->toDateString() }}"
                                           value="{{ old('new_date') }}"
                                           @if($serviceType === 'accommodation') data-stay-nights="{{ $action['stay_nights'] ?? '' }}" @endif
                                           @if($serviceType === 'activity') data-activity-date data-slots-url="{{ route($isAdmin ? 'admin.bookings.postpone.activity-slots' : 'operator.bookings.postpone.activity-slots', ['booking' => $booking->getKey()]) }}" data-loading-label="{{ __('booking_postponement.loading_slots') }}" data-empty-label="{{ __('booking_postponement.no_slots_available') }}" data-error-label="{{ __('booking_postponement.slots_load_error') }}" @endif
                                           required>
                                    @error('new_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if($serviceType === 'accommodation')
                                        <small class="form-text text-muted">
                                            {{ __('booking_postponement.calculated_checkout') }}:
                                            <span data-checkout-preview>{{ old('new_date') && $action['stay_nights'] ? \Carbon\Carbon::parse(old('new_date'))->addDays($action['stay_nights'])->format('M d, Y') : __('booking_postponement.select_check_in_date') }}</span>
                                        </small>
                                    @endif
                                </div>

                                @if($serviceType === 'transport')
                                    <div class="col-md-4">
                                        <label class="form-label" for="new_time_{{ $action['leg'] }}">{{ __('booking_postponement.new_time') }}</label>
                                        <input type="time"
                                               class="form-control @error('new_time') is-invalid @enderror"
                                               id="new_time_{{ $action['leg'] }}"
                                               name="new_time"
                                               value="{{ old('new_time') }}"
                                               required>
                                        @error('new_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @elseif($serviceType === 'activity')
                                    <div class="col-md-4">
                                        <label class="form-label" for="new_time_slot_id">{{ __('booking_postponement.new_time_slot') }}</label>
                                        <select class="form-select @error('new_time_slot_id') is-invalid @enderror"
                                                id="new_time_slot_id"
                                                name="new_time_slot_id"
                                                required>
                                            <option value="">{{ __('booking_postponement.select_date_first') }}</option>
                                        </select>
                                        <div class="form-text" data-activity-slot-status aria-live="polite"></div>
                                        @error('new_time_slot_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @else
                                    <div class="col-md-8 align-self-end">
                                        <small class="text-muted">{{ __('booking_postponement.accommodation_time_note') }}</small>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <label class="form-label" for="reason_{{ $serviceType }}_{{ $action['leg'] }}">
                                        {{ __('booking_postponement.reason') }}
                                    </label>
                                    <textarea class="form-control @error('reason') is-invalid @enderror"
                                              id="reason_{{ $serviceType }}_{{ $action['leg'] }}"
                                              name="reason"
                                              rows="2"
                                              maxlength="1000"
                                              required>{{ old('reason') }}</textarea>
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button class="btn btn-primary" type="submit">
                                        {{ __('booking_postponement.confirm') }}
                                    </button>
                                </div>
                            </div>
                    </form>
                @elseif(!$isAdmin && $action['active'])
                    <div class="alert alert-warning mt-3 mb-0">{{ $action['unavailable_reason'] }}</div>
                @endif
            </div>
        @endforeach

        @if($booking->scheduleChanges->isNotEmpty())
            <h6 class="mt-4">{{ __('booking_postponement.history') }}</h6>
            @foreach($booking->scheduleChanges as $change)
                <div class="border-top py-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <strong>{{ ucfirst($change->actor_role) }} · {{ $change->actor?->name ?? $change->actor?->email ?? __('booking_postponement.user_number', ['id' => $change->actor_id]) }}</strong>
                        <small class="text-muted">{{ $change->created_at?->timezone(config('app.timezone'))->format('M d, Y H:i') }}</small>
                    </div>
                    <div class="small mt-1">
                        <strong>{{ __('booking_postponement.previous_schedule') }}:</strong>
                        {{ collect($change->previous_schedule ?? [])->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key) . ': ' . ($value ?? '—'))->implode(' · ') }}
                    </div>
                    <div class="small">
                        <strong>{{ __('booking_postponement.new_schedule') }}:</strong>
                        {{ collect($change->new_schedule ?? [])->map(fn ($value, $key) => \Illuminate\Support\Str::headline($key) . ': ' . ($value ?? '—'))->implode(' · ') }}
                    </div>
                    <div class="small"><strong>{{ __('booking_postponement.reason') }}:</strong> {{ $change->reason }}</div>
                </div>
            @endforeach
        @endif
        <button class="btn btn-outline-secondary" type="button" data-postponement-cancel>
            {{ __('booking_postponement.cancel') }}
        </button>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('[data-postponement-toggle]').forEach((toggle) => {
        const panel = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!panel) return;

        toggle.addEventListener('click', () => {
            const open = panel.hidden;
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
        });

        panel.querySelector('[data-postponement-cancel]')?.addEventListener('click', () => {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        });
    });

    document.querySelectorAll('[data-stay-nights]').forEach((dateInput) => {
        const preview = dateInput.closest('[data-postponement-panel]')?.querySelector('[data-checkout-preview]');
        if (!preview) return;

        const updateCheckoutPreview = () => {
            if (!dateInput.value) {
                preview.textContent = @json(__('booking_postponement.select_check_in_date'));
                return;
            }
            const checkout = new Date(`${dateInput.value}T00:00:00`);
            checkout.setDate(checkout.getDate() + Number(dateInput.dataset.stayNights));
            preview.textContent = new Intl.DateTimeFormat(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            }).format(checkout);
        };

        dateInput.addEventListener('change', updateCheckoutPreview);
        updateCheckoutPreview();
    });

    document.querySelectorAll('[data-activity-date]').forEach((dateInput) => {
        const form = dateInput.closest('form');
        const select = form?.querySelector('select[name="new_time_slot_id"]');
        const status = form?.querySelector('[data-activity-slot-status]');
        if (!form || !select || !status) return;
        let activeRequest = null;

        const loadSlots = async () => {
            activeRequest?.abort();
            activeRequest = new AbortController();
            const requestController = activeRequest;
            select.replaceChildren(new Option(dateInput.dataset.loadingLabel, ''));
            select.disabled = true;
            status.textContent = '';
            if (!dateInput.value) {
                select.replaceChildren(new Option(@json(__('booking_postponement.select_date_first')), ''));
                select.disabled = false;
                return;
            }

            try {
                const url = new URL(dateInput.dataset.slotsUrl, window.location.href);
                url.searchParams.set('date', dateInput.value);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal: requestController.signal,
                });
                if (!response.ok) throw new Error(`Activity slots request failed (${response.status}).`);

                if (requestController.signal.aborted || dateInput.value !== url.searchParams.get('date')) return;
                const data = await response.json();
                const slots = Array.isArray(data.slots) ? data.slots : [];
                select.replaceChildren(new Option(
                    slots.length ? @json(__('booking_postponement.select_time_slot')) : dateInput.dataset.emptyLabel,
                    ''
                ));
                slots.forEach((slot) => {
                    select.add(new Option(
                        `${slot.display} (${slot.available} available)`,
                        slot.id
                    ));
                });
                if (!slots.length) status.textContent = dateInput.dataset.emptyLabel;
                const oldSlotId = @json(old('new_time_slot_id'));
                if (oldSlotId && slots.some((slot) => String(slot.id) === String(oldSlotId))) {
                    select.value = String(oldSlotId);
                }
            } catch (error) {
                if (requestController.signal.aborted) return;
                select.replaceChildren(new Option(dateInput.dataset.errorLabel, ''));
                status.textContent = dateInput.dataset.errorLabel;
                console.error(error);
            } finally {
                if (activeRequest === requestController) {
                    select.disabled = false;
                    activeRequest = null;
                }
            }
        };

        dateInput.addEventListener('change', loadSlots);
        if (dateInput.value) loadSlots();
    });
</script>
