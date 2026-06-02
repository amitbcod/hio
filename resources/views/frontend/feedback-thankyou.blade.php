@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Thank you</h1>
  <p>Thanks for submitting your feedback for trip #{{ $trip->id }}.</p>
</div>
@endsection
