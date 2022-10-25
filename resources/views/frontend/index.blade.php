@extends('frontend.layouts.app')

@section('content')
<div id="content">
    {{-- Sliders --}}
    <div class="home-banner-area mb-4 mt-4">
        <div class="container">
            <div class="row position-relative">
                <div class="col-lg-12 ">
                    @if (get_setting('home_slider_images') != null)
                        <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-arrows="true" data-dots="true" data-autoplay="true">
                            @php $slider_images = json_decode(get_setting('home_slider_images'), true);  @endphp
                            @foreach ($slider_images as $key => $value)
                                <div class="carousel-box">
                                    <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                        <img style="background-color:whitesmoke"
                                            class="d-block mw-100 img-fit lazyload rounded shadow-sm overflow-hidden"
                                            src="{{ uploaded_asset($slider_images[$key]) }}"
                                            alt="{{ env('APP_NAME')}} promo"
                                            height="457"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    {{-- Banner section 1 --}}
    @if (get_setting('home_banner1_images') != null)
        <div class="mb-4">
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

    @if (count($newest_products) > 0)
        <div id="section_newest">
            <section class="mb-4">
                <div class="container">
                    <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                                    {{ translate('New Products') }}
                                </span>
                            </h3>
                        </div>
                        <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-infinite='true'>
                            @foreach ($newest_products as $key => $new_product)
                            <div class="carousel-box">
                                @include('frontend.partials.product_box_1',['product' => $new_product])
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endif


    {{-- Banner Section 2 --}}
    @if (get_setting('home_banner2_images') != null)
        <div class="mb-4">
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
    <div id="section_featured">
        <section class="mb-4 mx-2">
            <div class="container py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                <div class="row">
                    <div class="col-xl-9">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                                    {{ translate('All Products') }}
                                </span>
                            </h3>
                        </div>
                        <div id="all_products_section" class="row gutters-10 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-2">

                        </div>
                    </div>
                    <div class="col-xl-3">
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
    </div>

    {{-- Banner Section 2 --}}
    @if (get_setting('home_banner3_images') != null)
        <div class="mb-4">
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
            // $.post('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_featured').html(data);
            //     AIZ.plugins.slickCarousel();
            // });
            // $.post('{{ route('home.section.home_categories') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_home_categories').html(data);
            //     AIZ.plugins.slickCarousel();
            // });
             $.post('{{ route('home.section.best_sellers') }}', {_token:'{{ csrf_token() }}'}, function(data){
                 $('#section_best_sellers').html(data);
                 AIZ.plugins.slickCarousel();
             });
        });


        var iCount = 2;
        var currentPage = 1;

        var home_url = "{{ route('home.section.all_products') }}";
        load_more(currentPage);

        var windowHeight = $(window).height();

		var content = $("#content");

		var contentYSpaces = 0;

        contentYSpaces += parseInt($(content).css("marginTop"));
		contentYSpaces += parseInt($(content).css("marginBottom"));
		contentYSpaces += parseInt($(content).css("paddingTop"));
		contentYSpaces += parseInt($(content).css("paddingBottom"));

        $(window).scroll(function() {
            var contentHeight = $("#content").height();

			var scrollTop = $(this).scrollTop();

			var diff  = contentHeight - (scrollTop+windowHeight-contentYSpaces);

            if(diff < 1000) {
                if (iCount == currentPage) {
                    iCount++;
                    load_more(currentPage);
                }
            }
        });

        function load_more(page){

            $.ajax({
                url: home_url + "?page=" + page,
                type: "get",
                datatype: "html",
                success: function(data)
                {
                    if(data!=""){
                        $("#all_products_section").append(data);
                        currentPage++;
                    }
                }
            });
        }
    </script>
@endsection
