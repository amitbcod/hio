@extends('frontend.layout')

@section('title', 'Your Guest Bookings | Holidays.io')
@section('meta_description', 'View and manage your guest bookings')

@section('content')

<section class="page-section guest-order-section" style="min-height: 60vh; padding: 60px 0; background: #f8f9fa;">
    <div class="wrap">
        <div class="guest-order-header" style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-size: 28px; font-weight: 600; margin-bottom: 10px;">Your Guest Bookings</h1>
            <p style="color: #666; font-size: 16px;">Found {{ $accommodationBookings->count() + $activityBookings->count() }} booking(s) for <strong>{{ $email }}</strong></p>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 30px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 30px;">
                {{ session('error') }}
            </div>
        @endif

        <div class="guest-bookings-list" style="max-width: 900px; margin: 0 auto;">

            {{-- Accommodation Bookings --}}
            @if($accommodationBookings->count() > 0)
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2c3e50;">
                        <i class="fa-solid fa-hotel" style="color: #19b5b5; margin-right: 10px;"></i>
                        Accommodation Bookings
                    </h2>

                    <div style="display: grid; gap: 15px;">
                        @foreach($accommodationBookings as $booking)
                            <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                                    <div style="flex: 1;">
                                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #2c3e50;">
                                            {{ $booking->accommodation->property_name ?? 'Accommodation' }}
                                        </h3>
                                        <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                            <i class="fa-solid fa-calendar" style="color: #19b5b5; margin-right: 8px; width: 16px;"></i>
                                            {{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}
                                        </p>
                                        <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                            <i class="fa-solid fa-user" style="color: #19b5b5; margin-right: 8px; width: 16px;"></i>
                                            {{ $booking->adults }} Adult(s)
                                            @if($booking->children > 0)
                                                , {{ $booking->children }} Child(ren)
                                            @endif
                                        </p>
                                        <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                            <i class="fa-solid fa-hashtag" style="color: #19b5b5; margin-right: 8px; width: 16px;"></i>
                                            Booking Ref: <strong>{{ $booking->booking_reference }}</strong>
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="background: #f0f7f7; padding: 10px 15px; border-radius: 4px; margin-bottom: 10px;">
                                            <p style="margin: 0; font-size: 12px; color: #666;">Status</p>
                                            <p style="margin: 5px 0 0 0; font-weight: 600; color: #19b5b5;">{{ $booking->booking_status }}</p>
                                        </div>
                                        <p style="margin: 0; font-size: 14px; color: #666;">
                                            {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Activity Bookings --}}
            @if($activityBookings->count() > 0)
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2c3e50;">
                        <i class="fa-solid fa-person-hiking" style="color: #19b5b5; margin-right: 10px;"></i>
                        Activity Bookings
                    </h2>

                    <div style="display: grid; gap: 15px;">
                        @foreach($activityBookings as $booking)
                            <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                                    <div style="flex: 1;">
                                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #2c3e50;">
                                            {{ $booking->activity->activity_name ?? 'Activity' }}
                                        </h3>
                                        @if($booking->variant_name)
                                            <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                                <i class="fa-solid fa-tag" style="color: #19b5b5; margin-right: 8px; width: 16px;"></i>
                                                {{ $booking->variant_name }}
                                            </p>
                                        @endif
                                        <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                            <i class="fa-solid fa-calendar" style="color: #19b5b5; margin-right: 8px; width: 16px;"></i>
                                            {{ $booking->activity_date->format('d/m/Y') }}
                                        </p>
                                        <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                            <i class="fa-solid fa-user" style="color: #19b5b5; margin-right: 8px; width: 16px;"></i>
                                            {{ $booking->adults }} Participant(s)
                                        </p>
                                        <p style="margin: 5px 0; color: #666; font-size: 14px;">
                                            <i class="fa-solid fa-hashtag" style="color: #19b5b5; margin-right: 8px; width: 16px;"></i>
                                            Booking Ref: <strong>{{ $booking->booking_reference }}</strong>
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="background: #f0f7f7; padding: 10px 15px; border-radius: 4px; margin-bottom: 10px;">
                                            <p style="margin: 0; font-size: 12px; color: #666;">Status</p>
                                            <p style="margin: 5px 0 0 0; font-weight: 600; color: #19b5b5;">{{ $booking->booking_status }}</p>
                                        </div>
                                        <p style="margin: 0; font-size: 14px; color: #666;">
                                            {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Send OTP Button --}}
            <div style="text-align: center; padding: 30px; background: white; border-radius: 8px; border: 1px solid #ddd;">
                <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                    To manage your bookings in detail, we'll send you a verification link to verify your email.
                </p>

                <form method="POST" action="{{ route('frontend.guest-order-search.send') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" style="padding: 12px 30px; background: #19b5b5; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background 0.3s;">
                        <i class="fa-solid fa-envelope" style="margin-right: 8px;"></i>
                        Send Me Link in Email
                    </button>
                </form>

                <p style="color: #999; font-size: 12px; margin-top: 15px;">
                    A verification link will be sent to {{ $email }}<br>
                    It expires in 15 minutes.
                </p>
            </div>

            {{-- Back Link --}}
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('frontend.home') }}" style="color: #19b5b5; text-decoration: none; font-weight: 500;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 5px;"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .guest-order-section {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .alert {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    button[type="submit"]:hover {
        background: #138080 !important;
        box-shadow: 0 2px 8px rgba(25, 181, 181, 0.3);
    }

    @media (max-width: 768px) {
        .guest-order-section {
            padding: 40px 0;
        }

        .guest-order-header h1 {
            font-size: 22px;
        }

        [style*="display: flex"] {
            flex-direction: column !important;
        }
    }
</style>

@endsection
