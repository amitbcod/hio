@extends('layouts.app')

@section('content')
<style>
.thankyou-box {
    max-width:400px;
    margin:0 auto;
    width:100%;
    text-align: center;
}
</style>
<div class="container">
  <div class="thankyou-box">
    <h1>Thank you</h1>
    <p>{{ __('feedback.thankyou', ['id' => $trip->id]) }}</p>
  </div>
</div>
@endsection
