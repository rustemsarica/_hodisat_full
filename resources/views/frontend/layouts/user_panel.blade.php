@extends('frontend.layouts.seller')
@section('content')
<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-start">
			@include('frontend.inc.seller_side_nav')
            {{-- @include('seller.inc.seller_sidenav') --}}
			<div class="aiz-user-panel">
				@yield('panel_content')
            </div>
        </div>
    </div>
</section>
@endsection
