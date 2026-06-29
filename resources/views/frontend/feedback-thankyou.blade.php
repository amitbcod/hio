@extends('layouts.app')

@section('content')
<div class="container">
  <div class="thankyou-box">
    <h1>Thank you</h1>
    <p>{{ __('feedback.thankyou', ['id' => $trip->id]) }}</p>
  </div>
</div>
@endsection
