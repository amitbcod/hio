<!-- Modal for Legal & Compliance (Advanced Settings) -->
<div class="modal fade" id="legalComplianceModal" tabindex="-1" aria-labelledby="legalComplianceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="legalComplianceModalLabel">LEGAL & COMPLIANCE</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ url('operator/register/step3-legal') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Business License Number *</label>
            <input type="text" name="business_license_number" class="form-control" required value="{{ old('business_license_number', $legal->business_license_number ?? '') }}">
          </div>
          <div class="form-group">
            <label>License Type *</label>
            <select name="license_type" class="form-control" required>
              <option value="">Select License Type</option>
              <option value="Accommodation" {{ (old('license_type', $legal->license_type ?? '') == 'Accommodation') ? 'selected' : '' }}>Accommodation</option>
              <option value="Tour Operator" {{ (old('license_type', $legal->license_type ?? '') == 'Tour Operator') ? 'selected' : '' }}>Tour Operator</option>
              <option value="Car Rental" {{ (old('license_type', $legal->license_type ?? '') == 'Car Rental') ? 'selected' : '' }}>Car Rental</option>
              <option value="Guide" {{ (old('license_type', $legal->license_type ?? '') == 'Guide') ? 'selected' : '' }}>Guide</option>
              <option value="Other" {{ (old('license_type', $legal->license_type ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>License Expiry Date *</label>
            <input type="date" name="license_expiry_date" class="form-control" required value="{{ old('license_expiry_date', $legal->license_expiry_date ?? '') }}">
          </div>
          <div class="form-group">
            <label>Service Package</label>
            <select name="service_package" class="form-control">
              <option value="HIO Listing Only" {{ (old('service_package', $legal->service_package ?? '') == 'HIO Listing Only') ? 'selected' : '' }}>HIO Listing Only</option>
              <option value="HIO Partner Standard" {{ (old('service_package', $legal->service_package ?? '') == 'HIO Partner Standard') ? 'selected' : '' }}>HIO Partner Standard</option>
              <option value="HIO Partner Pro" {{ (old('service_package', $legal->service_package ?? '') == 'HIO Partner Pro') ? 'selected' : '' }}>HIO Partner Pro</option>
              <option value="HIO Partner Elite" {{ (old('service_package', $legal->service_package ?? '') == 'HIO Partner Elite') ? 'selected' : '' }}>HIO Partner Elite</option>
              <option value="HIO Full Service" {{ (old('service_package', $legal->service_package ?? '') == 'HIO Full Service') ? 'selected' : '' }}>HIO Full Service</option>
            </select>
          </div>
          <div class="form-group">
            <label>Proof of License (PDF/JPEG)</label>
            <input type="file" name="proof_of_license" class="form-control">
            @if(!empty($legal->proof_of_license))
              <div class="mt-2">
                @php
                  $proofExt = pathinfo($legal->proof_of_license, PATHINFO_EXTENSION);
                @endphp
                @if(in_array(strtolower($proofExt), ['jpg','jpeg','png','gif']))
                  <img src="{{ asset('storage/' . $legal->proof_of_license) }}" alt="Proof of License" style="max-width:120px;max-height:120px;border:1px solid #ccc;">
                @elseif(strtolower($proofExt) == 'pdf')
                  <a href="{{ asset('storage/' . $legal->proof_of_license) }}" target="_blank">
                    <img src="https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/pdf.svg" alt="PDF" style="width:32px;vertical-align:middle;"> View PDF
                  </a>
                @endif
              </div>
            @endif
          </div>
          <div class="form-group">
            <label>Insurance Certificate</label>
            <input type="file" name="insurance_certificate" class="form-control">
            @if(!empty($legal->insurance_certificate))
              <div class="mt-2">
                @php
                  $insuranceExt = pathinfo($legal->insurance_certificate, PATHINFO_EXTENSION);
                @endphp
                @if(in_array(strtolower($insuranceExt), ['jpg','jpeg','png','gif']))
                  <img src="{{ asset('storage/' . $legal->insurance_certificate) }}" alt="Insurance Certificate" style="max-width:120px;max-height:120px;border:1px solid #ccc;">
                @elseif(strtolower($insuranceExt) == 'pdf')
                  <a href="{{ asset('storage/' . $legal->insurance_certificate) }}" target="_blank">
                    <img src="https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/pdf.svg" alt="PDF" style="width:32px;vertical-align:middle;"> View PDF
                  </a>
                @endif
              </div>
            @endif
          </div>
          <div class="form-group">
            <label>Signed Agreement</label>
            <input type="file" name="signed_agreement" class="form-control">
            @if(!empty($legal->signed_agreement))
              <div class="mt-2">
                @php
                  $signedExt = pathinfo($legal->signed_agreement, PATHINFO_EXTENSION);
                @endphp
                @if(in_array(strtolower($signedExt), ['jpg','jpeg','png','gif']))
                  <img src="{{ asset('storage/' . $legal->signed_agreement) }}" alt="Signed Agreement" style="max-width:120px;max-height:120px;border:1px solid #ccc;">
                @elseif(strtolower($signedExt) == 'pdf')
                  <a href="{{ asset('storage/' . $legal->signed_agreement) }}" target="_blank">
                    <img src="https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/pdf.svg" alt="PDF" style="width:32px;vertical-align:middle;"> View PDF
                  </a>
                @endif
              </div>
            @endif
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Save Compliance Info</button>
        </div>
      </form>
    </div>
  </div>
</div>
