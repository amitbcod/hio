@extends('layouts.app')

@section('content')
    <div class="wrap">
        <h2>Booking Widget</h2>

        @if(session('widget_token'))
            <div class="notice">
                <p>Your widget token was generated.</p>
            </div>
        @endif

        @if($widget)
            <p>Copy and paste this script into your external website:</p>
            <pre><code>&lt;script src="{{ url('/widget/booking-widget.js') }}" data-operator-token="{{ $widget->widget_token }}" async&gt;&lt;/script&gt;</code></pre>
            <form method="POST" action="{{ route('operator.booking-widget.generate') }}">
                @csrf
                <button type="submit" class="btn">Regenerate Token</button>
            </form>
        @else
            <form method="POST" action="{{ route('operator.booking-widget.generate') }}">
                @csrf
                <p>No widget token found. Generate one now.</p>
                <button type="submit" class="btn">Generate Booking Widget Script</button>
            </form>
        @endif
    </div>
@endsection
