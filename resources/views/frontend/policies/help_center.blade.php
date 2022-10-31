@extends('frontend.layouts.app')
@section('content')
<div class="container">
    <div class="row my-4">
        <div class="col-lg-6 m-auto">
            <div class="position-relative flex-grow-1 m-auto" style="max-width: 500px">
                <form action="{{ route('search') }}" method="GET" class="stop-propagation" id="searcForm">
                    <div class="d-flex position-relative align-items-center">
                        <div class="input-group">
                            <input type="text" class="border-0 border-lg form-control" id="search" name="keyword" @isset($query)
                                value="{{ $query }}"
                            @endisset placeholder="{{translate('Search')}}" autocomplete="off">
                            <div class="input-group-append d-none d-lg-block">
                                <div class="btn btn-primary" onclick="$('form#searcForm').submit();">
                                    <i class="la la-search la-flip-horizontal fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="typed-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100" style="min-height: 200px">
                    <div class="search-preloader absolute-top-center">
                        <div class="dot-loader"><div></div><div></div><div></div></div>
                    </div>
                    <div class="search-nothing d-none p-3 text-center fs-16">

                    </div>
                    <div id="search-content" class="text-left">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        @if ( get_setting('widget_one_labels',null,App::getLocale()) !=  null )
            @foreach (json_decode( get_setting('widget_one_labels',null,App::getLocale()), true) as $key => $value)
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <a href="{{ json_decode( get_setting('widget_one_links'), true)[$key] }}" class="opacity-80 hov-opacity-100 text-reset">
                        {{ $value }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 m-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-around ">
                        <i class="las la-paper-plane text-reset d-inline-block opacity-80 py-2 fs-24 m-auto"></i>
                        <a href="mailto:{{ get_setting('contact_email') }}" class="text-reset d-inline-block opacity-80 py-2 fs-24 m-auto">
                            <span>{{ get_setting('contact_email') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
