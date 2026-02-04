<div style="font-family: sans-serif; line-height: 1.4;">
    <h3>Agreement Confirmed</h3>
    <p>Business: <strong>{{ $business->legal_name }}</strong></p>
    <p>Agreement Type: <strong>{{ $collab->agreement_type }}</strong></p>
    <p>Status: <strong>{{ $collab->status }}</strong></p>
    @if($collab->agreement_file)
        <p>You can download the signed agreement here: <a href="{{ asset('storage/' . $collab->agreement_file) }}" target="_blank">Download Signed Agreement</a></p>
    @endif
    <p>Thanks,<br>HolidaysIO Team</p>
</div>