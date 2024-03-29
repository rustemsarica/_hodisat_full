@extends('frontend.layouts.app')

@section('content')
    <div id="content" class="container p-0">
        {{-- Sliders --}}
        <div class="home-banner-area mb-2 mt-4">
            <div class="container">
                <div class="row position-relative">
                        @if (get_setting('home_slider_images') != null)
                        <div class="col-lg-12">
                            @php $slider_images = json_decode(get_setting('home_slider_images'), true);  @endphp
                            @if (count($slider_images)>1)
                                <div class="aiz-carousel mobile-img-auto-height" data-arrows="false" data-dots="false" data-autoplay="true" data-infinite="true">
                                    @foreach ($slider_images as $key => $value)
                                        <div class="carousel-box">
                                            <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                                <img style="background-color:var(--soft-primary);"
                                                    class="d-block mw-100 img-fit lazyload rounded shadow-sm"
                                                    src="{{ uploaded_asset($slider_images[$key]) }}"
                                                    alt="{{ env('APP_NAME')}} promo"
                                                    height="440"
                                                >
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="m-auto mobile-img-auto-height">
                                    <a href="{{ json_decode(get_setting('home_slider_links'), true)[0] }}">
                                        <img style="background-color:var(--soft-primary);"
                                            class="d-block mw-100 img-fit lazyload rounded shadow-sm"
                                            src="{{ uploaded_asset($slider_images[0]) }}"
                                            alt="{{ env('APP_NAME')}} promo"
                                            height="440"
                                        >
                                    </a>
                                </div>
                            @endif
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
                                    <a href="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}" class="d-block  text-reset">
                                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_1_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- @if (count($newest_products) > 0)
            <div id="section_newest">
                <section class="mb-2">
                    <div class="container px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                                    {{ translate('New Products') }}
                                </span>
                            </h3>
                        </div>
                        <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-infinite='true'>
                            @foreach ($newest_products as $key => $product)
                            <div class="carousel-box">
                                <div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-2 has-transition bg-white">
                                    @if(discount_in_percentage($product) > 0)
                                        <span class="badge-custom">{{ translate('OFF') }}<span class="box ml-1 mr-0">&nbsp;{{discount_in_percentage($product)}}%</span></span>
                                    @endif
                                    <div class="position-relative">
                                        @php
                                            $product_url = route('product', $product->slug);
                                        @endphp
                                        <a href="{{ $product_url }}" class="d-block">
                                            <img style="background-color:whitesmoke"
                                                class="img-fit lazyload lazyload-image mx-auto h-140px h-md-210px"
                                                src="{{ static_asset($product->thumbnail!=null ? $product->thumbnail->file_name : 'assets/img/placeholder.jpg') }}"
                                                alt="{{  $product->name  }}"
                                            >
                                        </a>
                                        @if ($product->current_stock==0)
                                            <span class="absolute-center text-center fs-20 text-white fw-600 p-2 lh-1-8" style="background-color: #455a64; opacity:0.7; width:100%;">
                                                {{ translate('Sold') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="p-md-3 p-2 d-flex justify-content-between">
                                        <div class="fs-18">
                                            @if(home_base_price($product->unit_price) != home_discounted_base_price($product))
                                                <del class="fw-600 opacity-80 mr-1">{{ home_base_price($product->unit_price) }}</del>
                                            @endif
                                            <span class="fw-700 text-primary">{{ home_discounted_base_price($product) }}</span>
                                        </div>
                                        <div class="c-pointer fs-24" onclick="addToWishList({{ $product->id }})">
                                            <i class="la la-heart-o"></i>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        @endif --}}


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
            <div class="container py-4 px-md-2 py-md-3 bg-white shadow-sm rounded">
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
                        @include('frontend.partials.best_sellers_section')
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

@section('script')
    <script>
        $(document).ready(function(){

            AIZ.plugins.slickCarousel();
        });


        var iCount = 2;
        var currentPage = 1;

        var home_url = "{{ route('home.section.all_products') }}";
        load_more(currentPage);

        // var windowHeight = $(window).height();

        // var content = $("#content");

        // var contentYSpaces = 0;

        // contentYSpaces += parseInt($(content).css("marginTop"));
        // contentYSpaces += parseInt($(content).css("marginBottom"));
        // contentYSpaces += parseInt($(content).css("paddingTop"));
        // contentYSpaces += parseInt($(content).css("paddingBottom"));

        // $(window).scroll(function() {
        //     var contentHeight = $("#content").height();

        //     var scrollTop = $(this).scrollTop();

        //     var diff  = contentHeight - (scrollTop+windowHeight-contentYSpaces);

        //     if(diff < 1000) {
        //         if (iCount == currentPage) {
        //             iCount++;
        //             load_more(currentPage);
        //         }
        //     }
        // });

        function load_more(page){
            $.ajax({
                url: home_url + "?page=" + page,
                type: "get",
                datatype: "html",
                success: function(data)
                {
                    if(data!=""){
                        $("#all_products_section").append(data);
    // data.data.forEach(element => {
    // var html = '<div class="col">';
    // html +='<div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-2 has-transition bg-white">';
    // if(element.has_discount){
    //     html +='<span class="badge-custom">{{ translate("OFF") }}<span class="box ml-1 mr-0">&nbsp;'+element.discount+'</span></span>';
    // }
    // html +='<div class="position-relative">'
    // html +='<a href="'+element.links.details+'" class="d-block"><img style="background-color:whitesmoke" class="img-fit lazyload lazyload-image mx-auto h-140px h-md-210px" src="'+element.thumbnail_image+'" alt="'+element.name+'" > </a>'
    // if (element.current_stock==0){
    //     html +='<span class="absolute-center text-center fs-20 text-white fw-600 p-2 lh-1-8" style="background-color: #455a64; opacity:0.7; width:100%;">{{ translate("Sold") }}</span>'
    // }
    // html +='</div><div class="p-md-3 p-2 d-flex justify-content-between"><div class="fs-18">';
    // html +='<span class="fw-700 text-primary">'+element.main_price+'</span></div><div class="c-pointer fs-24 d-flex justify-content-between" onclick="addToWishList('+element.id+')">';
    // if(element.is_in_wishlist){
    //     html +='<i class="la la-heart" style="color: var(--red)"></i>';
    // }else{
    //     html +='<i class="la la-heart-o"></i>';
    // }
    // html +='<div class="pl-1 fs-16 opacity-80 m-auto">'+element.wish_count+'</div></div></div></div>'
    // html+='</div>';
    // $("#all_products_section").append(html);
    // });
                        currentPage++;
                    }
                }
            });
        }
    </script>
@endsection

