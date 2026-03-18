(function(){
    // Fees & Surcharges popup injector (non-invasive)
    if (window.__feesPopupInjected) return;
    window.__feesPopupInjected = true;

    const accommodationIdMatch = window.location.pathname.match(/accommodation\/(\d+)/);
    const accommodationId = accommodationIdMatch ? accommodationIdMatch[1] : '0';

    const style = document.createElement('style');
    style.textContent = `
    #fees-popup-btn {position:fixed;right:18px;bottom:18px;z-index:2147483647;background:#6f42c1;color:#fff;border:none;border-radius:6px;padding:10px 14px;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.15);font-weight:600}
    #fees-popup-panel {position:fixed;right:18px;bottom:68px;z-index:2147483647;width:360px;max-width:calc(100% - 40px);background:#fff;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,0.2);padding:12px;font-family:Arial,sans-serif}
    #fees-popup-panel h4{margin:0 0 8px 0;font-size:15px}
    #fees-popup-panel label{display:block;font-size:12px;margin-top:8px}
    #fees-popup-panel input[type="number"], #fees-popup-panel select, #fees-popup-panel input[type="text"]{width:100%;padding:6px 8px;margin-top:4px;border:1px solid #ddd;border-radius:4px;font-size:13px}
    #fees-popup-panel .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
    #fees-popup-panel .badge{display:inline-block;padding:2px 6px;background:#f0f0f0;border-radius:4px;font-size:12px;margin-left:6px}
    `;
    document.head.appendChild(style);

    // No floating button — controlled via page 'Advanced Settings' button

    const panel = document.createElement('div');
    panel.id = 'fees-popup-panel';
    panel.style.display = 'none';
    panel.innerHTML = `
        <h4>Fees & Surcharges</h4>
        <div>
            <label>Cleaning Fee (per stay)</label>
            <input type="number" id="fees_cleaning" step="0.01" min="0" placeholder="e.g. 100.00" />

            <label>Resort Fee (per night)</label>
            <input type="number" id="fees_resort" step="0.01" min="0" placeholder="e.g. 10.00" />

            <label>Early Check-in Fee</label>
            <div style="display:flex;gap:8px;">
                <select id="fees_early_type" style="width:120px;padding:6px 8px;border:1px solid #ddd;border-radius:4px;">
                    <option value="">Type</option>
                    <option value="percent">Percent</option>
                    <option value="fixed">Fixed</option>
                </select>
                <input type="number" id="fees_early_value" step="0.01" min="0" placeholder="Value" />
            </div>

            <label>Late Check-out Fee</label>
            <div style="display:flex;gap:8px;">
                <select id="fees_late_type" style="width:120px;padding:6px 8px;border:1px solid #ddd;border-radius:4px;">
                    <option value="">Type</option>
                    <option value="percent">Percent</option>
                    <option value="fixed">Fixed</option>
                </select>
                <input type="number" id="fees_late_value" step="0.01" min="0" placeholder="Value" />
            </div>

            <div class="actions">
                <button id="fees_cancel" style="padding:6px 10px;border-radius:4px;border:1px solid #ccc;background:#fff;">Cancel</button>
                <button id="fees_save" style="padding:6px 10px;border-radius:4px;border:none;background:#6f42c1;color:#fff;">Save</button>
            </div>
            <div id="fees_message" style="margin-top:8px;font-size:13px;color:#333;display:none"></div>
        </div>
    `;
    document.body.appendChild(panel);

    function getKey() {
        return 'fees_accommodation_' + accommodationId + '_property';
    }

    function showMessage(msg, success){
        const el = document.getElementById('fees_message');
        el.style.display = 'block';
        el.style.color = success ? '#28a745' : '#d9534f';
        el.textContent = msg;
        setTimeout(()=> el.style.display='none', 3000);
    }

    // Panel visibility is controlled by the page's Advanced Settings button

    document.getElementById('fees_cancel').addEventListener('click', ()=>{
        panel.style.display = 'none';
    });

    document.getElementById('fees_save').addEventListener('click', ()=>{
        // validation
        const earlyType = document.getElementById('fees_early_type').value;
        const earlyVal = document.getElementById('fees_early_value').value;
        const lateType = document.getElementById('fees_late_type').value;
        const lateVal = document.getElementById('fees_late_value').value;

        if (earlyType === 'percent' && earlyVal !== '' && (Number(earlyVal) < 0 || Number(earlyVal) > 100)) {
            showMessage('Early check-in percent must be 0-100', false); return;
        }
        if (lateType === 'percent' && lateVal !== '' && (Number(lateVal) < 0 || Number(lateVal) > 100)) {
            showMessage('Late checkout percent must be 0-100', false); return;
        }

        const payload = {
            accommodation_id: accommodationId,
            cleaning_fee: document.getElementById('fees_cleaning').value || null,
            resort_fee: document.getElementById('fees_resort').value || null,
            early_checkin_type: earlyType || null,
            early_checkin_value: earlyVal || null,
            late_checkout_type: lateType || null,
            late_checkout_value: lateVal || null,
            saved_at: new Date().toISOString()
        };

        // send to server
        (async function(){
            try{
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const headers = {'Content-Type':'application/json'};
                if(tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');

                const res = await fetch('/operator/accommodation/' + accommodationId + '/additional-fees', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if(res.ok && data.success){
                    try{ localStorage.setItem(getKey(), JSON.stringify(payload)); }catch(e){}
                    showMessage('Saved', true);
                } else {
                    console.error('save fees failed', data);
                    showMessage(data.message || 'Failed to save', false);
                }
            }catch(e){
                console.error(e); showMessage('Failed to save', false);
            }
        })();
    });

    // expose helper to read stored fees
    window.getFees = function(accomId){
        const propertyKey = 'fees_accommodation_' + accomId + '_property';
        const result = {property: null, rooms: {}};
        try{
            const prop = localStorage.getItem(propertyKey);
            if(prop) result.property = JSON.parse(prop);
            // find room overrides
            for(let i=0;i<localStorage.length;i++){
                const k = localStorage.key(i);
                if(!k) continue;
                const m = k.match(new RegExp('^fees_accommodation_' + accomId + '_room_(\\d+)$'));
                if(m){
                    result.rooms[m[1]] = JSON.parse(localStorage.getItem(k));
                }
            }
        }catch(e){console.error(e)}
        return result;
    };

    // auto-load existing property-level fees: prefer server, fallback to localStorage
    (async function prefill(){
            try{
                const res = await fetch('/operator/accommodation/' + accommodationId + '/additional-fees', {credentials: 'same-origin'});
            if(res.ok){
                const json = await res.json();
                if(json.success && json.data){
                    const o = json.data;
                    if(o.cleaning_fee !== null) document.getElementById('fees_cleaning').value = o.cleaning_fee;
                    if(o.resort_fee !== null) document.getElementById('fees_resort').value = o.resort_fee;
                    if(o.early_checkin_type) document.getElementById('fees_early_type').value = o.early_checkin_type;
                    if(o.early_checkin_value !== null) document.getElementById('fees_early_value').value = o.early_checkin_value;
                    if(o.late_checkout_type) document.getElementById('fees_late_type').value = o.late_checkout_type;
                    if(o.late_checkout_value !== null) document.getElementById('fees_late_value').value = o.late_checkout_value;
                    return;
                }
            }
        }catch(e){/*ignore*/}

        // fallback to localStorage
        try{
            const key = 'fees_accommodation_' + accommodationId + '_property';
            const v = localStorage.getItem(key);
            if(v){
                const o = JSON.parse(v);
                if(o.cleaning_fee) document.getElementById('fees_cleaning').value = o.cleaning_fee;
                if(o.resort_fee) document.getElementById('fees_resort').value = o.resort_fee;
                if(o.early_checkin_type) document.getElementById('fees_early_type').value = o.early_checkin_type;
                if(o.early_checkin_value) document.getElementById('fees_early_value').value = o.early_checkin_value;
                if(o.late_checkout_type) document.getElementById('fees_late_type').value = o.late_checkout_type;
                if(o.late_checkout_value) document.getElementById('fees_late_value').value = o.late_checkout_value;
            }
        }catch(e){/*ignore*/}
    })();

})();
