@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                @if(session('error'))
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <strong>✗ {{ session('error') }}</strong>
                </div>
                @endif

                <div style="background:#fff;border-radius:16px;padding:40px;box-shadow:0 2px 16px rgba(0,0,0,0.07);text-align:center;">
                    <div style="font-size:64px;margin-bottom:24px;">✨</div>
                    <h2 style="font-weight:700;margin:0 0 12px 0;">Create New Activity</h2>
                    <p style="color:#666;margin:0 0 32px 0;font-size:16px;">
                        Add a new experience, tour, service, or activity to your portfolio
                    </p>

                    <form method="POST" action="{{ route('operator.activity.store') }}" style="max-width:500px;margin:0 auto;">
                        @csrf

                        <div style="margin-bottom:20px;">
                            <p style="color:#999;font-size:13px;margin:0 0 12px 0;">You'll provide detailed information in the next step. Let's get started!</p>
                        </div>

                        <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:14px 32px;border-radius:4px;border:none;cursor:pointer;font-size:16px;font-weight:600;">
                            Create Activity & Start Setup
                        </button>

                        <br><br>

                        <a href="{{ route('operator.activity.index') }}" style="color:#19b5b5;text-decoration:none;font-size:14px;">
                            ← Back to Activities
                        </a>
                    </form>
                </div>

                {{-- Info Cards --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:32px;">
                    <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #19b5b5;">
                        <h5 style="margin:0 0 8px 0;font-weight:600;">Step 1: Basic Information</h5>
                        <p style="margin:0;color:#666;font-size:14px;">Provide service type, activity name, categories, location details, and comprehensive descriptions.</p>
                    </div>

                    <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #999;">
                        <h5 style="margin:0 0 8px 0;font-weight:600;color:#999;">Step 2: Pricing (Coming Soon)</h5>
                        <p style="margin:0;color:#999;font-size:14px;">Set pricing, availability calendars, and booking rules.</p>
                    </div>

                    <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #999;">
                        <h5 style="margin:0 0 8px 0;font-weight:600;color:#999;">Step 3: Media (Coming Soon)</h5>
                        <p style="margin:0;color:#999;font-size:14px;">Upload high-quality photos and videos showcasing your activity.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
