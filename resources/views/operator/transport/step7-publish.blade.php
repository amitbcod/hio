when @extends('layouts.app')

@section('title', 'Transport Step 6 | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @php $currentStep = 8; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 8: Publish</h2>
                <p style="margin:8px 0 0 0;color:#666;">Submit your transport service for approval.</p>
            </div>

            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                @if((($transport->approval_status ?? null) === 'Approved') || $transport->status === \App\Models\Transport::STATUS_ACTIVE)
                    <h4 style="margin-top:0;">Published</h4>
                    <p>Your transport listing has been approved and published.</p>
                    <p>Published at: <strong>{{ $transport->published_at ? $transport->published_at->format('Y-m-d H:i') : 'N/A' }}</strong></p>
                    <p>It is now visible to travelers according to your publication settings.</p>
                @elseif(((($transport->approval_status ?? '') === 'Pending') && $transport->submitted_for_approval_at) || (($transport->status === \App\Models\Transport::STATUS_IN_REVIEW) && $transport->submitted_for_approval_at))
                    <h4 style="margin-top:0;">Waiting for admin approval</h4>
                    <p>Your transport listing was submitted for admin review on <strong>{{ ($transport->submitted_for_approval_at ?? $transport->updated_at ?? $transport->created_at) ? ($transport->submitted_for_approval_at ?? $transport->updated_at ?? $transport->created_at)->format('Y-m-d H:i') : 'N/A' }}</strong>. You will be notified when a decision is made.</p>
                    <p>If the administrator approves your listing, it will become published automatically.</p>
                @else
                    <form method="POST" action="{{ route('operator.transport.submit-approval', $transport->id) }}">
                        @csrf
                        <p>When you're ready, submit your transport listing for review.</p>
                        <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Submit for Approval</button>
                    </form>
                @endif
             </div>
        </div>
    </div>
</div>
@endsection
