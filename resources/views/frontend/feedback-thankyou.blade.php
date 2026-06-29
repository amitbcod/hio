@extends('layouts.app')

@section('content')
<style>
  .thank-page {
    max-width: 470px;
    margin: 60px auto;
    width: 100%;
    text-align: center;
  }
.thankyou-box {
    width:100%;
    text-align: center;
    background: #ffffff;
    border: 1px solid #e5dfd6;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
    padding: 28px;
    margin: 10px 0 0;
    color: #6b7280;
    line-height: 1.8;
}
.thankyou-box h1{
  margin: 0;
    font-size: 34px;
    font-family: 'Roboto Slab', Georgia, serif;
}
a.btn-home{
      color: #ff8a00;
    font-weight: 700;
    text-decoration: none;
}
a.btn-home:hover{
      color: #333;
}
</style>
<div class="container">
  <div class="thank-page">
    <div class="thankyou-box">
      <h1>Thank you</h1>
      <p>{{ __('feedback.thankyou', ['id' => $trip->id]) }}</p>
    </div>
    <a href="{{ url('/') }}" class="btn-home">Go to Home</a>
  </div>
</div>
@endsection
