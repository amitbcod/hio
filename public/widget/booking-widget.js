/* Booking Widget (vanilla ES6) - injects a sticky Book Now button and popup form
   Usage: include <script src="https://yourdomain.com/widget/booking-widget.js" data-operator-token="TOKEN" async></script>
*/
(function () {
    'use strict';

    const currentScript = document.currentScript || (function () {
        const scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1];
    })();

    const operatorToken = currentScript && currentScript.getAttribute('data-operator-token') ? currentScript.getAttribute('data-operator-token') : null;
    const origin = new URL(currentScript.src).origin;

    if (!operatorToken) {
        console.warn('Booking widget: missing operator token');
        return;
    }

    // Validate token before rendering
    fetch(origin + '/widget/validate/' + encodeURIComponent(operatorToken), { method: 'GET', credentials: 'omit' })
        .then(res => {
            if (!res.ok) throw new Error('Invalid token');
            return res.json();
        })
        .then(data => {
            if (!data.valid) throw new Error('Token invalid');
            initWidget();
        })
        .catch(err => {
            console.warn('Booking widget not initialized:', err.message);
        });

    function initWidget() {
        // Create host element
        const host = document.createElement('div');
        host.id = 'booking-widget-host-' + Math.random().toString(36).substr(2, 6);
        document.body.appendChild(host);

        const shadow = host.attachShadow({ mode: 'closed' });

        // Styles (scoped)
        const style = document.createElement('style');
        style.textContent = `
            :host { font-family: Arial, Helvetica, sans-serif; }
            .bw-button { position: fixed; right: 18px; bottom: 18px; z-index: 2147483647; background: #ff8c00; color: #fff; border: none; padding: 14px 18px; border-radius: 28px; box-shadow: 0 6px 18px rgba(0,0,0,0.2); cursor: pointer; font-weight:600; }
            .bw-panel { position: fixed; right: 18px; bottom: 132px; z-index: 2147483647; width: 420px; max-width: calc(100% - 40px); background: #fff; border-radius: 8px; box-shadow: 0 12px 40px rgba(0,0,0,0.3); overflow: visible; transform: translateY(10px); transition: transform .24s ease, opacity .18s ease; opacity:0; }
            .bw-panel.open { transform: translateY(0); opacity:1; }
            .bw-header { display:flex; gap:8px; padding:12px; background:#f7f7f7; align-items:center; }
            .bw-tabs { display:flex; gap:6px; }
            .bw-tab { padding:8px 10px; border-radius:6px; cursor:pointer; font-size:14px; }
            .bw-tab.active { background:#ff8c00; color:#fff; }
            .bw-body { padding:12px; }
            .bw-row { margin-bottom:10px; }
            .bw-label { font-size:12px; color:#333; margin-bottom:6px; display:block; }
            .bw-input, .bw-select { width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; }
            .bw-date-input-wrapper { position:relative; display:block; width:100%; }
            .bw-date-display { position:relative; z-index:1; min-height:38px; display:flex; align-items:center; justify-content:space-between; padding:8px 36px 8px 8px; border:1px solid #ddd; border-radius:6px; background:#fff; color:#333; cursor:pointer; pointer-events:auto; }
            .bw-date-display::after { content: "📅"; position:absolute; right:12px; font-size:16px; color:#666; pointer-events:none; }
            .bw-date-input { position:absolute; top:0; left:0; right:0; bottom:0; opacity:0; cursor:pointer; z-index:10; pointer-events:none; }
            .bw-time-input-wrapper { position:relative; }
            .bw-time-display { min-height:38px; display:flex; align-items:center; padding:8px; border:1px solid #ddd; border-radius:6px; background:#fff; color:#333; cursor:pointer; }
            .bw-time-popup { position:absolute; left:0; right:0; top:100%; margin-top:10px; background:#fff; border:1px solid #ddd; border-radius:10px; box-shadow:0 14px 30px rgba(0,0,0,0.18); padding:20px; display:none; z-index:2147483647; min-width:320px; width: calc(100% + 24px); transform: translateX(-12px); }
            .bw-time-popup.open { display:block; }
            .bw-time-column { display:flex; flex-direction:column; gap:10px; margin-bottom:14px; }
            .bw-time-column label { font-size:12px; color:#666; }
            .bw-time-select { width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; }
            .bw-time-actions { text-align:right; }
            .bw-time-ok { background:#ff8c00; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; }
            .bw-actions { display:flex; gap:8px; padding:12px; justify-content:flex-end; }
            .bw-proceed { background:#ff8c00; color:#fff; border:none; padding:10px 14px; border-radius:6px; cursor:pointer; }
            @media (max-width:480px){ .bw-panel{ right:10px; left:10px; bottom:70px; width:auto; } }
        `;

        shadow.appendChild(style);

        const button = document.createElement('button');
        button.className = 'bw-button';
        button.textContent = 'Book Now';
        button.addEventListener('click', togglePanel);

        const panel = document.createElement('div');
        panel.className = 'bw-panel';

        panel.innerHTML = `
            <div class="bw-header">
                <div class="bw-tabs">
                    <div class="bw-tab active" data-service="accommodation">Accommodation</div>
                    <div class="bw-tab" data-service="activity">Activity</div>
                    <div class="bw-tab" data-service="transport">Transport</div>
                </div>
            </div>
            <div class="bw-body">
                <form id="bw-form">
                    <!-- Accommodation -->
                    <div class="bw-service" data-service-panel="accommodation">
                        <div class="bw-row bw-date-field">
                            <label class="bw-label">Check-in</label>
                            <div class="bw-date-input-wrapper">
                                <div class="bw-date-display" data-date-display-for="check_in">dd/mm/yy</div>
                                <input class="bw-date-input" name="check_in" type="date">
                            </div>
                        </div>
                        <div class="bw-row bw-date-field">
                            <label class="bw-label">Check-out</label>
                            <div class="bw-date-input-wrapper">
                                <div class="bw-date-display" data-date-display-for="check_out">dd/mm/yy</div>
                                <input class="bw-date-input" name="check_out" type="date">
                            </div>
                        </div>
                        <div class="bw-row"><label class="bw-label">Guests</label><input class="bw-input" name="guests" type="number" min="1" value="2"></div>
                        <div class="bw-row"><label class="bw-label">Rooms</label><input class="bw-input" name="rooms" type="number" min="1" value="1"></div>
                    </div>
                    <!-- Activity -->
                    <div class="bw-service" data-service-panel="activity" style="display:none;">
                        <div class="bw-row bw-date-field">
                            <label class="bw-label">Activity date</label>
                            <div class="bw-date-input-wrapper">
                                <div class="bw-date-display" data-date-display-for="activity_date">dd/mm/yy</div>
                                <input class="bw-date-input" name="activity_date" type="date">
                            </div>
                        </div>
                        <div class="bw-row"><label class="bw-label">Travellers</label><input class="bw-input" name="travellers" type="number" min="1" value="1"></div>
                    </div>
                    <!-- Transport -->
                    <div class="bw-service" data-service-panel="transport" style="display:none;">
                        <div class="bw-row bw-date-field">
                            <label class="bw-label">Pickup date</label>
                            <div class="bw-date-input-wrapper">
                                <div class="bw-date-display" data-date-display-for="pickup_date">dd/mm/yy</div>
                                <input class="bw-date-input" name="pickup_date" type="date">
                            </div>
                        </div>
                        <div class="bw-row bw-time-field">
                            <label class="bw-label">Pickup time</label>
                            <div class="bw-time-input-wrapper">
                                <div class="bw-time-display" data-time-display>--:--</div>
                                <input class="bw-time-hidden" name="pickup_time" type="hidden">
                                <div class="bw-time-popup" data-time-popup>
                                    <div class="bw-time-column">
                                        <label>Hour</label>
                                        <select class="bw-time-select" data-time-hour></select>
                                    </div>
                                    <div class="bw-time-column">
                                        <label>Minute</label>
                                        <select class="bw-time-select" data-time-minute></select>
                                    </div>
                                    <div class="bw-time-actions"><button type="button" class="bw-time-ok">Set</button></div>
                                </div>
                            </div>
                        </div>
                        <div class="bw-row"><label class="bw-label">Passengers</label><input class="bw-input" name="passengers" type="number" min="1" value="2"></div>
                    </div>
                    <div class="bw-actions"><button type="button" class="bw-proceed">Proceed</button></div>
                </form>
            </div>
        `;

        shadow.appendChild(panel);
        shadow.appendChild(button);

        // Tab switching
        const tabs = panel.querySelectorAll('.bw-tab');
        tabs.forEach(t => t.addEventListener('click', (ev) => {
            tabs.forEach(x => x.classList.remove('active'));
            ev.target.classList.add('active');
            switchService(ev.target.getAttribute('data-service'));
        }));

        function switchService(service) {
            panel.querySelectorAll('.bw-service').forEach(div => {
                const active = div.getAttribute('data-service-panel') === service;
                div.style.display = active ? 'block' : 'none';
                div.querySelectorAll('input,select,textarea').forEach(input => {
                    input.disabled = !active;
                });
            });
            panel.setAttribute('data-current-service', service);
        }

        function togglePanel() {
            panel.classList.toggle('open');
        }

        // Proceed handler
        panel.querySelector('.bw-proceed').addEventListener('click', () => {
            const service = panel.getAttribute('data-current-service') || 'accommodation';
            const form = panel.querySelector('form');
            const fd = new FormData(form);
            const params = {};
            fd.forEach((v, k) => { if (v !== null && v !== undefined && v !== '') params[k] = v; });

            // Map field names to expected query keys
            const mapping = {
                'destination': 'destination', 'check_in': 'check_in', 'check_out': 'check_out', 'guests': 'guests', 'rooms': 'rooms',
                'activity_destination': 'destination', 'activity_date': 'activity_date', 'travellers': 'travellers',
                'pickup': 'pickup', 'dropoff': 'dropoff', 'pickup_date': 'pickup_date', 'pickup_time': 'pickup_time', 'passengers': 'passengers'
            };

            const qp = {};
            Object.keys(params).forEach(k => {
                if (mapping[k]) qp[mapping[k]] = params[k];
            });

            // Redirect to tracking endpoint on our site which will validate token and forward
            const qs = new URLSearchParams(qp);
            const redirectUrl = origin + '/widget/track-redirect?token=' + encodeURIComponent(operatorToken) + '&service=' + encodeURIComponent(service) + '&' + qs.toString();
            window.location.href = redirectUrl;
        });

        function formatDateDisplay(value) {
            if (!value) return '';
            const parts = value.split('-');
            if (parts.length !== 3) return value;
            return `${parts[2].padStart(2, '0')}/${parts[1].padStart(2, '0')}/${parts[0].slice(-2)}`;
        }

        function setupDateDisplays(form) {
            form.querySelectorAll('.bw-date-input').forEach((input) => {
                const display = form.querySelector(`.bw-date-display[data-date-display-for="${input.name}"]`);
                if (!display) return;
                const refresh = () => {
                    display.textContent = input.value ? formatDateDisplay(input.value) : 'dd/mm/yy';
                };
                input.addEventListener('input', refresh);

                display.addEventListener('click', (event) => {
                    event.preventDefault();
                    input.focus();
                    if (typeof input.showPicker === 'function') {
                        input.showPicker();
                    } else {
                        input.click();
                    }
                });
                refresh();
            });
        }

        function setupTimePicker(form) {
            const timeWrapper = form.querySelector('.bw-time-input-wrapper');
            if (!timeWrapper) return;
            const timeDisplay = timeWrapper.querySelector('.bw-time-display');
            const hiddenInput = timeWrapper.querySelector('.bw-time-hidden');
            const popup = timeWrapper.querySelector('[data-time-popup]');
            const hourSelect = popup.querySelector('[data-time-hour]');
            const minuteSelect = popup.querySelector('[data-time-minute]');
            const okButton = popup.querySelector('.bw-time-ok');

            const hours = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0'));
            const minutes = Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0'));

            hours.forEach((hour) => {
                const opt = document.createElement('option');
                opt.value = hour;
                opt.textContent = hour;
                hourSelect.appendChild(opt);
            });
            minutes.forEach((minute) => {
                const opt = document.createElement('option');
                opt.value = minute;
                opt.textContent = minute;
                minuteSelect.appendChild(opt);
            });

            const updateMinuteOptions = () => {
                minuteSelect.querySelectorAll('option').forEach((option) => {
                    option.disabled = false;
                });
            };

            const refreshDisplay = () => {
                timeDisplay.textContent = hiddenInput.value || '--:--';
            };

            const openPopup = () => {
                syncSelects();
                popup.classList.add('open');
            };

            const closePopup = () => {
                popup.classList.remove('open');
            };

            const syncSelects = () => {
                const value = hiddenInput.value || '';
                const [hour = '01', minute = '00'] = value.split(':');
                hourSelect.value = hours.includes(hour.padStart(2, '0')) ? hour.padStart(2, '0') : '01';
                minuteSelect.value = minutes.includes(minute) ? minute : '00';
                updateMinuteOptions();
            };

            timeDisplay.addEventListener('click', (event) => {
                event.stopPropagation();
                if (popup.classList.contains('open')) {
                    closePopup();
                } else {
                    openPopup();
                }
            });

            okButton.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const hour = hourSelect.value;
                const minute = minuteSelect.value;
                hiddenInput.value = hour === '24' ? '24:00' : `${hour}:${minute}`;
                refreshDisplay();
                closePopup();
            });

            popup.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            document.addEventListener('click', (event) => {
                if (!timeWrapper.contains(event.target)) {
                    closePopup();
                }
            });

            syncSelects();
            refreshDisplay();
        }

        setupDateDisplays(panel);
        setupTimePicker(panel);

        // default current service and disable inactive service inputs
        switchService('accommodation');
    }

})();
