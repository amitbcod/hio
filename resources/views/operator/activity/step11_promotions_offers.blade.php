@extends('layouts.app')

@section('content')
    <!-- Quill WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar -->
            @php $currentStep = 11; @endphp
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Header -->
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:start;">
                        <div>
                            <h4 style="font-weight:600;color:#333;margin:0;">Step 11: Promotions & Offers</h4>
                            <p style="margin:4px 0 0 0;font-size:13px;color:#666;">{{ $activity->activity_name }}</p>
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;font-size:12px;color:#999;">Service ID: {{ $activity->id }}</p>
                            <p style="margin:4px 0 0 0;font-size:12px;color:#19b5b5;font-weight:600;">Promotions: {{ $promotions->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                @if($errors->any())
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                    @foreach($errors->all() as $error)
                        <div style="margin-bottom:4px;">• {{ $error }}</div>
                    @endforeach
                </div>
                @endif

                @if(session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                <!-- Info Box -->
                <div style="background:#e3f2fd;border-left:4px solid #2196f3;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#1565c0;">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> Create promotional offers with discounts, validity dates, and approval workflow. Apply specific discounts to activity variations.
                </div>

                <!-- Add Promotion Button -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h5 style="margin:0;font-weight:600;color:#333;font-size:14px;">Promotions List</h5>
                    <button type="button" onclick="openPromotionForm()" style="padding:8px 16px;background:#19b5b5;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;">
                        <i class="fas fa-plus"></i> Add Promotion
                    </button>
                </div>

                <!-- Promotions List -->
                @if($promotions->count() > 0)
                    <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:#f5f5f5;border-bottom:2px solid #e0e0e0;">
                                        <th style="padding:12px;text-align:left;font-weight:600;">Campaign ID</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Campaign Name</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Discount</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Valid From</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Valid To</th>
                                        <th style="padding:12px;text-align:center;font-weight:600;">Status</th>
                                        <th style="padding:12px;text-align:center;font-weight:600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($promotions as $promotion)
                                        <tr style="border-bottom:1px solid #e0e0e0;">
                                            <td style="padding:12px;"><code style="background:#f0f0f0;padding:2px 6px;border-radius:3px;font-size:11px;">{{ $promotion->campaign_id }}</code></td>
                                            <td style="padding:12px;">
                                                <strong>{{ $promotion->campaign_name }}</strong><br>
                                                <small style="color:#666;">{{ Str::limit($promotion->campaign_description, 40) }}</small>
                                            </td>
                                            <td style="padding:12px;">
                                                @if($promotion->discount_type === 'Percentage')
                                                    <span style="background:#e8f5e9;color:#2e7d32;padding:4px 8px;border-radius:3px;font-weight:600;">{{ $promotion->discount_value }}%</span>
                                                @else
                                                    <span style="background:#fff3e0;color:#e65100;padding:4px 8px;border-radius:3px;font-weight:600;">${{ number_format($promotion->discount_value, 2) }}</span>
                                                @endif
                                            </td>
                                            <td style="padding:12px;">{{ $promotion->promo_valid_from->format('d/m/Y') }}</td>
                                            <td style="padding:12px;">{{ $promotion->promo_valid_to->format('d/m/Y') }}</td>
                                            <td style="padding:12px;text-align:center;">
                                                <span style="padding:4px 8px;border-radius:3px;font-size:11px;font-weight:600;background:{{ $promotion->approval_status === 'Published' ? '#c8e6c9' : ($promotion->approval_status === 'Pending Approval' ? '#fff9c4' : '#e0e0e0') }};color:{{ $promotion->approval_status === 'Published' ? '#1b5e20' : ($promotion->approval_status === 'Pending Approval' ? '#f57f17' : '#424242') }};">
                                                    {{ $promotion->approval_status }}
                                                </span>
                                            </td>
                                            <td style="padding:12px;text-align:center;">
                                                <button type="button" class="promotion-edit" data-promotion-id="{{ $promotion->promotion_id }}" data-variant-ids="{{ json_encode($promotion->variant_ids) }}" data-campaign-name="{{ $promotion->campaign_name }}" data-campaign-description="{{ $promotion->campaign_description }}" data-specifications="{{ $promotion->specifications }}" data-inclusions="{{ $promotion->inclusions }}" data-exclusions="{{ $promotion->exclusions }}" data-discount-type="{{ $promotion->discount_type }}" data-discount-value="{{ $promotion->discount_value }}" data-promo-valid-from="{{ $promotion->promo_valid_from->format('Y-m-d') }}" data-promo-valid-to="{{ $promotion->promo_valid_to->format('Y-m-d') }}" data-non-refundable="{{ $promotion->non_refundable }}" data-approval-status="{{ $promotion->approval_status }}" style="padding:4px 8px;background:#007bff;border:none;border-radius:3px;color:#fff;cursor:pointer;font-size:11px;">Edit</button>

                                                <form action="{{ route('operator.activity.step11.delete', [$activity->id, $promotion->promotion_id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this promotion?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="padding:4px 8px;background:#ffebee;color:#c62828;border:none;border-radius:3px;cursor:pointer;font-size:11px;margin-left:4px;">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div style="background:#fff;border-radius:12px;padding:24px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <p style="color:#999;font-size:14px;">No promotions added yet.</p>
                    </div>
                @endif

                <!-- Promotion Form -->
                <div id="promotionFormContainer" style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;display:none;border:2px solid #19b5b5;">
                    <h5 id="promotionFormTitle" style="margin:0 0 20px 0;font-weight:600;color:#333;">Add Promotion</h5>

                    <form id="promotionForm" method="POST" action="{{ route('operator.activity.step11.store', $activity->id) }}">
                        @csrf
                        <input type="hidden" name="_method" id="promotionFormMethod" value="POST">

                        <!-- Service Variation Selection -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:8px;">Service Variations * <span style="color:#666;font-weight:normal;font-size:12px;">(Select at least one)</span></label>
                            <div style="background:#f9f9f9;border:1px solid #ddd;border-radius:4px;padding:12px;max-height:200px;overflow-y:auto;">
                                @foreach($variants as $variant)
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:8px;">
                                        <input type="checkbox" name="variant_ids[]" value="{{ $variant->variant_id }}" style="cursor:pointer;">
                                        <span style="font-size:13px;">{{ $variant->variant_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Campaign Name -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;">Campaign Name *</label>
                            <input type="text" name="campaign_name" id="campaignName" class="form-control" required style="font-size:13px;" placeholder="e.g., Summer Special Discount">
                        </div>

                        <!-- Campaign Description -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;">Campaign Description <span style="color:#666;font-weight:normal;font-size:12px;">(max 250 chars)</span></label>
                            <textarea name="campaign_description" id="campaignDescription" maxlength="250" style="display:none;"></textarea>
                            <div id="campaignDescriptionEditor" style="height:120px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                                <small style="color:#666;">Character count: <span id="descCharCount">0</span>/250</small>
                                <small id="campaignDescriptionError" style="color:#d93025;display:none;"></small>
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;">Specifications / Itinerary *</label>
                            <textarea name="specifications" id="specifications" required style="display:none;"></textarea>
                            <div id="specificationsEditor" style="height:170px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                        </div>

                        <!-- Inclusions -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;">Inclusions <span style="color:#666;font-weight:normal;font-size:12px;">(What's included)</span></label>
                            <textarea name="inclusions" id="inclusions" style="display:none;"></textarea>
                            <div id="inclusionsEditor" style="height:120px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                        </div>

                        <!-- Exclusions -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;">Exclusions <span style="color:#666;font-weight:normal;font-size:12px;">(What's NOT included)</span></label>
                            <textarea name="exclusions" id="exclusions" style="display:none;"></textarea>
                            <div id="exclusionsEditor" style="height:120px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">Discount Type *</label>
                                <select name="discount_type" id="discountType" class="form-control" required style="font-size:13px;">
                                    <option value="">-- Select Discount Type --</option>
                                    <option value="Percentage">Percentage (%)</option>
                                    <option value="Amount">Fixed Amount ($)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">Discount Value *</label>
                                <div style="display:flex;gap:8px;">
                                    <input type="number" name="discount_value" id="discountValue" class="form-control" required min="0.01" step="0.01" style="font-size:13px;" placeholder="Amount">
                                    <span id="discountUnit" style="padding:8px 12px;background:#f0f0f0;border-radius:4px;align-self:center;font-weight:600;">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">Valid From *</label>
                                <input type="date" name="promo_valid_from" id="promoValidFrom" class="form-control" required style="font-size:13px;">
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">Valid To *</label>
                                <input type="date" name="promo_valid_to" id="promoValidTo" class="form-control" required style="font-size:13px;">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">Non-Refundable *</label>
                                <select name="non_refundable" id="nonRefundable" class="form-control" required style="font-size:13px;">
                                    <option value="">-- Select --</option>
                                    <option value="Yes">Yes - Non-Refundable</option>
                                    <option value="No">No - Refundable</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">Approval Status *</label>
                                <select name="approval_status" id="approvalStatus" class="form-control" required style="font-size:13px;">
                                    <option value="Draft">Draft</option>
                                    <option value="Pending Approval">Pending Approval</option>
                                    <option value="Published">Published</option>
                                </select>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                            <button type="button" onclick="closePromotionForm()" style="padding:10px 20px;background:#f0f0f0;color:#333;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Cancel</button>
                            <button type="submit" style="padding:10px 20px;background:#19b5b5;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;"><span id="promotionSubmitText">Save Promotion</span></button>
                        </div>
                    </form>
                </div>

                <!-- Navigation -->
                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                    <a href="{{ route('operator.activity.step10.show', $activity->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;text-decoration:none;">← Back to Step 10</a>
                    @if($promotions->count() > 0)
                     <form method="POST" action="{{ route('operator.activity.update', $activity->id) }}" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="mark_step" value="step11_promotions_offers">
                        <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:8px 12px;border-radius:4px;border:none;cursor:pointer;">Complete Step 11 →</button>
                    </form> 
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const promotionEditors = {};

        function getEditorTextLength(editor) {
            if (!editor) {
                return 0;
            }
            return editor.getText().replace(/\n$/, '').length;
        }

        function syncEditorToTextarea(textareaId) {
            const textarea = document.getElementById(textareaId);
            const editor = promotionEditors[textareaId];
            if (textarea && editor) {
                textarea.value = editor.root.innerHTML;
            }
        }

        function setEditorContent(textareaId, value) {
            const textarea = document.getElementById(textareaId);
            const editor = promotionEditors[textareaId];
            const content = value || '';

            if (textarea) {
                textarea.value = content;
            }
            if (editor) {
                editor.root.innerHTML = content;
            }
        }

        function clearAllEditors() {
            setEditorContent('campaignDescription', '');
            setEditorContent('specifications', '');
            setEditorContent('inclusions', '');
            setEditorContent('exclusions', '');
            document.getElementById('descCharCount').textContent = '0';
        }

        function updateCampaignDescriptionCount() {
            const countElement = document.getElementById('descCharCount');
            const editor = promotionEditors.campaignDescription;
            if (!countElement || !editor) {
                return;
            }
            const length = getEditorTextLength(editor);
            countElement.textContent = String(length);
            countElement.style.color = length > 250 ? '#d93025' : '#666';
        }

        function validateCampaignDescription() {
            const editor = promotionEditors.campaignDescription;
            const errorElement = document.getElementById('campaignDescriptionError');
            if (!editor || !errorElement) {
                return true;
            }
            const length = getEditorTextLength(editor);
            if (length > 250) {
                errorElement.style.display = 'block';
                errorElement.textContent = 'Campaign Description exceeds 250 characters.';
                return false;
            } else {
                errorElement.style.display = 'none';
                errorElement.textContent = '';
                return true;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const editorConfigs = [
                {
                    textareaId: 'campaignDescription',
                    editorId: 'campaignDescriptionEditor',
                    placeholder: 'Brief description of the campaign...',
                    maxLength: 250
                },
                {
                    textareaId: 'specifications',
                    editorId: 'specificationsEditor',
                    placeholder: "Describe what's included, package details, special features..."
                },
                {
                    textareaId: 'inclusions',
                    editorId: 'inclusionsEditor',
                    placeholder: 'E.g., - Equipment rental\n- Professional guide\n- Lunch\n- Photos'
                },
                {
                    textareaId: 'exclusions',
                    editorId: 'exclusionsEditor',
                    placeholder: 'E.g., - Hotel pickup\n- Travel insurance\n- Alcoholic beverages'
                }
            ];

            editorConfigs.forEach(function(config) {
                const textarea = document.getElementById(config.textareaId);
                const editorDiv = document.getElementById(config.editorId);

                if (!textarea || !editorDiv) {
                    return;
                }

                const editor = new Quill('#' + config.editorId, {
                    theme: 'snow',
                    placeholder: config.placeholder,
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                if (textarea.value) {
                    editor.root.innerHTML = textarea.value;
                }

                editor.on('text-change', function() {
                    syncEditorToTextarea(config.textareaId);

                    if (config.textareaId === 'campaignDescription') {
                        updateCampaignDescriptionCount();
                    }
                });

                promotionEditors[config.textareaId] = editor;
            });

            document.querySelectorAll('.promotion-edit').forEach(button => {
                button.addEventListener('click', function() {
                    openPromotionForm(this.dataset);
                });
            });

            document.getElementById('discountType').addEventListener('change', function() {
                document.getElementById('discountUnit').textContent = this.value === 'Percentage' ? '%' : '$';
            });

            const form = document.getElementById('promotionForm');
            if (form) {
                form.addEventListener('submit', function(event) {
                    syncEditorToTextarea('campaignDescription');
                    syncEditorToTextarea('specifications');
                    syncEditorToTextarea('inclusions');
                    syncEditorToTextarea('exclusions');
                    
                    if (!validateCampaignDescription()) {
                        event.preventDefault();
                        document.getElementById('campaignDescriptionError').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                });
            }

            updateCampaignDescriptionCount();

            @if(old('campaign_name') || old('discount_type'))
                openPromotionForm();
            @endif
        });

        function openPromotionForm(data = null) {
            const form = document.getElementById('promotionForm');
            form.reset();
            clearAllEditors();

            const isEdit = data && data.promotionId;
            document.getElementById('promotionFormMethod').value = isEdit ? 'PUT' : 'POST';
            document.getElementById('promotionFormTitle').innerText = isEdit ? 'Edit Promotion' : 'Add Promotion';
            document.getElementById('promotionSubmitText').innerText = isEdit ? 'Update Promotion' : 'Save Promotion';
            form.action = isEdit
                ? '{{ route('operator.activity.step11.update', [$activity->id, '__PROMOTION_ID__']) }}'.replace('__PROMOTION_ID__', data.promotionId)
                : '{{ route('operator.activity.step11.store', $activity->id) }}';

            if (isEdit) {
                const variantIds = JSON.parse(data.variantIds || '[]');
                document.querySelectorAll('input[name="variant_ids[]"]').forEach(checkbox => {
                    checkbox.checked = variantIds.includes(parseInt(checkbox.value));
                });

                document.getElementById('campaignName').value = data.campaignName || '';
                setEditorContent('campaignDescription', data.campaignDescription || '');
                setEditorContent('specifications', data.specifications || '');
                setEditorContent('inclusions', data.inclusions || '');
                setEditorContent('exclusions', data.exclusions || '');

                document.getElementById('discountType').value = data.discountType || '';
                document.getElementById('discountUnit').textContent = data.discountType === 'Percentage' ? '%' : '$';
                document.getElementById('discountValue').value = data.discountValue || '';
                document.getElementById('promoValidFrom').value = data.promoValidFrom || '';
                document.getElementById('promoValidTo').value = data.promoValidTo || '';
                document.getElementById('nonRefundable').value = data.nonRefundable || '';
                document.getElementById('approvalStatus').value = data.approvalStatus || '';

                updateCampaignDescriptionCount();
            }

            document.getElementById('promotionFormContainer').style.display = 'block';
            document.getElementById('promotionFormContainer').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function closePromotionForm() {
            document.getElementById('promotionFormContainer').style.display = 'none';
            document.getElementById('promotionForm').reset();
            clearAllEditors();
            document.getElementById('discountUnit').textContent = '%';
        }
    </script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
