@php
    $normalizedStatus = strtolower(trim((string) ($status ?? '')));
    $normalizedStatus = preg_replace('/[\s_-]+/', ' ', $normalizedStatus) ?? '';
    $statusTone = match ($normalizedStatus) {
        'paid', 'verified & settled', 'verified settled', 'verified and settled',
        'confirmed', 'completed' => 'success',
        'pending', 'pending verification', 'processing' => 'warning',
        'cancel', 'cancelled', 'canceled', 'failed', 'refunded', 'rejected' => 'danger',
        'scheduled' => 'info',
        default => 'neutral',
    };
@endphp
<span class="operator-status-badge operator-status-badge--{{ $statusTone }}">{{ $statusBadgeLabel ?? $status }}</span>
