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
            .bw-panel { position: fixed; right: 18px; bottom: 72px; z-index: 2147483647; width: 360px; max-width: calc(100% - 40px); background: #fff; border-radius: 8px; box-shadow: 0 12px 40px rgba(0,0,0,0.3); overflow: hidden; transform: translateY(10px); transition: transform .24s ease, opacity .18s ease; opacity:0; }
            .bw-panel.open { transform: translateY(0); opacity:1; }
            .bw-header { display:flex; gap:8px; padding:12px; background:#f7f7f7; align-items:center; }
            .bw-tabs { display:flex; gap:6px; }
            .bw-tab { padding:8px 10px; border-radius:6px; cursor:pointer; font-size:14px; }
            .bw-tab.active { background:#ff8c00; color:#fff; }
            .bw-body { padding:12px; }
            .bw-row { margin-bottom:10px; }
            .bw-label { font-size:12px; color:#333; margin-bottom:6px; display:block; }
            .bw-input, .bw-select { width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; }
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
                        <div class="bw-row"><label class="bw-label">Destination</label><input class="bw-input" name="destination" type="text"></div>
                        <div class="bw-row"><label class="bw-label">Check-in</label><input class="bw-input" name="check_in" type="date"></div>
                        <div class="bw-row"><label class="bw-label">Check-out</label><input class="bw-input" name="check_out" type="date"></div>
                        <div class="bw-row"><label class="bw-label">Guests</label><input class="bw-input" name="guests" type="number" min="1" value="2"></div>
                        <div class="bw-row"><label class="bw-label">Rooms</label><input class="bw-input" name="rooms" type="number" min="1" value="1"></div>
                    </div>
                    <!-- Activity -->
                    <div class="bw-service" data-service-panel="activity" style="display:none;">
                        <div class="bw-row"><label class="bw-label">Destination</label><input class="bw-input" name="activity_destination" type="text"></div>
                        <div class="bw-row"><label class="bw-label">Activity date</label><input class="bw-input" name="activity_date" type="date"></div>
                        <div class="bw-row"><label class="bw-label">Travellers</label><input class="bw-input" name="travellers" type="number" min="1" value="1"></div>
                    </div>
                    <!-- Transport -->
                    <div class="bw-service" data-service-panel="transport" style="display:none;">
                        <div class="bw-row"><label class="bw-label">Pickup location</label><input class="bw-input" name="pickup" type="text"></div>
                        <div class="bw-row"><label class="bw-label">Drop location</label><input class="bw-input" name="dropoff" type="text"></div>
                        <div class="bw-row"><label class="bw-label">Pickup date</label><input class="bw-input" name="pickup_date" type="date"></div>
                        <div class="bw-row"><label class="bw-label">Pickup time</label><input class="bw-input" name="pickup_time" type="time"></div>
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
                div.style.display = div.getAttribute('data-service-panel') === service ? 'block' : 'none';
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

        // default current service
        panel.setAttribute('data-current-service', 'accommodation');
    }

})();
