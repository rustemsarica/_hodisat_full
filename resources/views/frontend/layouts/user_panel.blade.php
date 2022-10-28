@extends('frontend.layouts.app')
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
@section('scripts')

<script src="{{ static_asset('assets/js/uppy.min.js') }}" ></script>
<script src="{{ static_asset('assets/js/footable.min.js') }}" ></script>
<script src="{{ static_asset('assets/js/chart.min.js') }}" ></script>
@endsection
