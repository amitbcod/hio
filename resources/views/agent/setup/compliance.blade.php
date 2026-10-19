@extends('layouts.agent')
@section('title', 'Agent Compliance')
@section('content')
<h1 class="h3 mb-4">Agent Compliance</h1>
<form method="POST" action="{{ route('agent.compliance.save') }}" enctype="multipart/form-data" class="card"><div class="card-body">@csrf
    <div class="mb-3"><label class="form-label">Business Registration No. *</label><input class="form-control" name="business_registration_number" value="{{ old('business_registration_number', $document?->business_registration_number) }}" required></div>
    <div class="mb-3"><label class="form-label">IATA No.</label><input class="form-control" name="iata_number" value="{{ old('iata_number', $document?->iata_number) }}"></div>
    <div class="mb-3"><label class="form-label">Tax Number</label><input class="form-control" name="tax_number" value="{{ old('tax_number', $document?->tax_number) }}"></div>
    <div class="mb-3"><label class="form-label">Document Type</label><select class="form-select" name="document_type"><option value="">Select</option>@foreach(['Trade Licence','Insurance','Contract ID Proof'] as $type)<option @selected(old('document_type', $document?->document_type) === $type)>{{ $type }}</option>@endforeach</select></div>
    <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Issue Date *</label><input class="form-control" type="date" name="issue_date" value="{{ old('issue_date', $document?->issue_date) }}" required></div><div class="col-md-6 mb-3"><label class="form-label">Expiry Date *</label><input class="form-control" type="date" name="expiry_date" value="{{ old('expiry_date', $document?->expiry_date) }}" required></div></div>
    <div class="mb-3"><label class="form-label">Document File {{ $document ? '(optional to replace)' : '*' }}</label><input class="form-control" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" {{ $document ? '' : 'required' }}></div>
    @if($document)<p><a href="{{ route('agent.compliance.download', $document) }}">Download current document</a></p>@endif
    <button class="btn btn-primary">Save and Continue</button>
</div></form>
@endsection
