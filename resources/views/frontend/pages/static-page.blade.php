@extends('frontend.layout')

@section('title', $page->title . ' | Holidays.io')
@section('meta_description', $page->meta_description ?: 'Static page content on Holidays.io')

@section('content')
<section class="page-section">
    <div class="wrap">
        <h1>{{ $page->title }}</h1>
        <div>{!! $content !!}</div>
    </div>
</section>
@endsection
