<div class="form-group">
    <label>Service Type</label>
    <select name="service_type" class="form-control">
        <option value="accommodation" {{ (old('service_type', $service) == 'accommodation') ? 'selected' : '' }}>Accommodation</option>
        <option value="activity" {{ (old('service_type', $service) == 'activity') ? 'selected' : '' }}>Activity</option>
    </select>
</div>

<div class="form-group">
    <label>Policy Type</label>
    <select name="policy_type" class="form-control">
        @php
            $types = [];
            if(($service ?? '') == 'accommodation') {
                $types = ['Amendment Policy','Cancellation Policy','Security Deposit Policy','House Rules'];
            } else {
                $types = ['Amendment Policy','Cancellation Policy'];
            }
        @endphp
        @foreach($types as $type)
            <option value="{{ $type }}" {{ old('policy_type', optional($template)->policy_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Active</label>
    <div class="checkbox">
        <label>
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($template)->is_active ?? true) ? 'checked' : '' }}>
            Enable this policy template
        </label>
    </div>
</div>

<div class="form-group">
    <label>Content (HTML)</label>
    <textarea name="content" id="content" class="form-control" rows="10" style="display:none;">{{ old('content', optional($template)->content) }}</textarea>
    <div id="content_editor" class="wysiwyg-editor" style="min-height:160px;background:#fff;border:1px solid #ddd;border-radius:4px;margin-bottom:8px;"></div>
</div>

