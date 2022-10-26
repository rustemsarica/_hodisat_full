@extends('frontend.layouts.app')

@section('content')
    <div id="content">
        {{-- Sliders --}}
        <div class="home-banner-area mb-2 mt-4">
            <div class="container">
                <div class="row position-relative">
                        @if (get_setting('home_slider_images') != null)
                            <div class="aiz-carousel dots-inside-bottom m-auto mobile-img-auto-height" data-arrows="false" data-dots="false" data-autoplay="true">
                                @php $slider_images = json_decode(get_setting('home_slider_images'), true);  @endphp
                                @foreach ($slider_images as $key => $value)
                                    <div class="carousel-box">
                                        <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                            <img style="background-color:whitesmoke"
                                                class="d-block mw-100 img-fit lazyload rounded shadow-sm"
                                                src="{{ uploaded_asset($slider_images[$key]) }}"
                                                alt="{{ env('APP_NAME')}} promo"
                                                height="457"
                                            >
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                </div>
            </div>
        </div>


        {{-- Banner section 1 --}}
        @if (get_setting('home_banner1_images') != null)
            <div class="mb-2">
                <div class="container">
                    <div class="row gutters-10">
                        @php $banner_1_imags = json_decode(get_setting('home_banner1_images')); @endphp
                        @foreach ($banner_1_imags as $key => $value)
                            <div class="col-xl col-md-6">
                                <div class="mb-3 mb-lg-0">
                                    <a href="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}" class="d-block text-reset">
                                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_1_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif



        {{-- Banner Section 2 --}}
        @if (get_setting('home_banner2_images') != null)
            <div class="mb-2">
                <div class="container">
                    <div class="row gutters-10">
                        @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
                        @foreach ($banner_2_imags as $key => $value)
                            <div class="col-xl col-md-6">
                                <div class="mb-3 mb-lg-0">
                                    <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}" class="d-block text-reset">
                                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_2_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Featured Section --}}
        <section class="mb-2 mx-2">
            <div class="container py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                <div class="row">
                    <div class="col-xl-9">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <a href="{{route('search')}}"><span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                                    {{ translate('All Products') }}
                                </span></a>
                            </h3>
                        </div>
                        <div id="all_products_section" class="row gutters-10 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-2">
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <a href="{{ route('blog') }}"><span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Blog') }}</span></a>
                            </h3>
                        </div>
                        @include('frontend.partials.blog_section')
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                                    {{ translate('Best Sellers') }}
                                </span>
                            </h3>
                        </div>
                        {{-- Best Seller --}}
                        <div id="section_best_sellers">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Banner Section 2 --}}
        @if (get_setting('home_banner3_images') != null)
            <div class="mb-2">
                <div class="container">
                    <div class="row gutters-10">
                        @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
                        @foreach ($banner_3_imags as $key => $value)
                            <div class="col-xl col-md-6">
                                <div class="mb-3 mb-lg-0">
                                    <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}" class="d-block text-reset">
                                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_3_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection


