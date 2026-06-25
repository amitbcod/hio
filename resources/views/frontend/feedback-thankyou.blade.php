@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Thank you</h1>
  <p>{{ __('feedback.thankyou', ['id' => $trip->id]) }}</p>
</div>
@endsection
