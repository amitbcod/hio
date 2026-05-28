@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div id="sidebar" class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9" style="margin-top: 30px;">
                {{-- Header --}}
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h2 style="font-weight:700;margin:0;">Shared Trip Links</h2>
                        <p style="margin:8px 0 0 0;color:#666;">Create shareable trip collections for customers</p>
                    </div>
                    <a href="{{ route('operator.shared-carts.create') }}" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;text-decoration:none;font-size:14px;font-weight:600;">
                        + Create New Link
                    </a>
                </div>

                @if(session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                @if($sharedCarts->count() > 0)
                    {{-- Shared Carts List Table --}}
                    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);overflow:hidden;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f5f5f5;border-bottom:1px solid #e0e0e0;">
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Trip Title</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Items</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Status</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Expires</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Created</th>
                                    <th style="padding:16px;text-align:center;font-weight:600;font-size:13px;color:#666;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sharedCarts as $cart)
                                <tr style="border-bottom:1px solid #e0e0e0;transition:background 0.2s;">
                                    <td style="padding:16px;">
                                        <strong>{{ $cart->title }}</strong>
                                        <br>
                                        <small style="color:#999;font-family:monospace;">{{ substr($cart->token, 0, 20) }}...</small>
                                    </td>
                                    <td style="padding:16px;">
                                        <span style="display:inline-block;background:#e3f2fd;color:#1565c0;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">
                                            {{ count($cart->items ?? []) }} item{{ count($cart->items ?? []) !== 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td style="padding:16px;">
                                        @php
                                            $isActive = $cart->isActive();
                                            $statusColor = $isActive ? '#e8f5e9' : '#ffebee';
                                            $statusText = $isActive ? '#2e7d32' : '#c62828';
                                            $statusLabel = $isActive ? 'Active' : 'Inactive';
                                        @endphp
                                        <span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;background:{{ $statusColor }};color:{{ $statusText }};">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td style="padding:16px;">
                                        @if($cart->expires_at)
                                            <small style="color:#666;">{{ $cart->expires_at->format('M d, Y') }}</small>
                                        @else
                                            <small style="color:#999;">No expiry</small>
                                        @endif
                                    </td>
                                    <td style="padding:16px;">
                                        <small style="color:#666;">{{ $cart->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td style="padding:16px;text-align:center;">
                                        <button type="button" class="copy-link-btn" data-link="{{ route('frontend.booking.shared', $cart->token) }}" style="display:inline-block;padding:6px 12px;background:#e8f4f8;text-decoration:none;border-radius:3px;font-size:12px;color:#19b5b5;border:1px solid #b3e5fc;cursor:pointer;margin-right:4px;font-weight:600;">
                                            📋 Copy Link
                                        </button>
                                        <a href="{{ route('operator.shared-carts.show', $cart->id) }}" style="display:inline-block;padding:6px 12px;background:#f0f0f0;text-decoration:none;border-radius:3px;font-size:12px;color:#333;">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);padding:48px;text-align:center;">
                        <div style="font-size:48px;margin-bottom:16px;">🔗</div>
                        <h4 style="color:#333;margin-bottom:8px;">No Shared Trip Links Yet</h4>
                        <p style="color:#666;margin-bottom:24px;">Create your first shareable trip link to let customers add pre-selected items to their cart.</p>
                        <a href="{{ route('operator.shared-carts.create') }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:12px 24px;border-radius:4px;text-decoration:none;font-weight:600;">
                            + Create First Link
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.copy-link-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const link = this.getAttribute('data-link');
                navigator.clipboard.writeText(link).then(() => {
                    const originalText = this.textContent;
                    const originalBg = this.style.background;
                    this.textContent = '✓ Copied!';
                    this.style.background = 'rgba(76, 175, 80, 0.15)';
                    this.style.color = '#388e3c';
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = originalBg;
                        this.style.color = '#19b5b5';
                    }, 2000);
                }).catch(() => {
                    alert('Failed to copy link. Please copy manually.');
                });
            });
        });
    </script>
@endsection
