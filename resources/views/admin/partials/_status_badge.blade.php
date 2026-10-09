@php
    $normalizedStatus = strtolower(trim((string) ($status ?? '')));
    $normalizedStatus = preg_replace('/[\s_-]+/', ' ', $normalizedStatus) ?? '';
    $statusTone = match ($normalizedStatus) {
        'paid', 'verified & settled', 'verified and settled', 'verified settled', 'confirmed' => 'success',
        'pending', 'pending verification', 'processing' => 'warning',
        'cancel', 'cancelled', 'canceled', 'failed', 'refunded', 'rejected' => 'danger',
        default => 'neutral',
    };
@endphp
<span class="admin-status-badge admin-status-badge--{{ $statusTone }}">{{ $badgeLabel ?? $status }}</span>
