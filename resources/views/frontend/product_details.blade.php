@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->name }}@stop

@section('meta_description'){{ $detailedProduct->description }}@stop

@section('meta_keywords'){{ str_replace(" ",",",$detailedProduct->name).",".str_replace(" ",",",$detailedProduct->description) }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->name }}">
    <meta itemprop="description" content="{{ $detailedProduct->description }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->thumbnail_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->name }}">
    <meta name="twitter:description" content="{{ $detailedProduct->description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->thumbnail_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->name }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->thumbnail_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->description }}" />
    <meta property="og:site_name" content="{{ get_setting('name') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:price:currency"
        content="{{ \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
@endsection

@section('content')
    <section class="mb-4 pt-3">
        <div class="container">
            <div class="bg-white shadow-sm rounded p-3">
                <div class="row">
                    <div class="col-xl-5 col-lg-6 mb-4">
                        <div class="sticky-top z-3 row gutters-10">
                            @php
                                $photos = explode(',', $detailedProduct->photos);
                            @endphp
                            <div class="col order-1 order-md-2">
                                <div class="aiz-carousel product-gallery" data-nav-for='.product-gallery-thumb'
                                    data-fade='true' data-auto-height='true'>
                                    @foreach ($photos as $key => $photo)
                                        <div class="carousel-box img-zoom rounded">
                                            <img class="img-fluid lazyload"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 col-md-auto w-md-80px order-2 order-md-1 mt-3 mt-md-0">
                                <div class="aiz-carousel product-gallery-thumb" data-items='5'
                                    data-nav-for='.product-gallery' data-vertical='true' data-vertical-sm='false'
                                    data-focus-select='true' data-arrows='true'>
                                    @foreach ($photos as $key => $photo)
                                        <div class="carousel-box c-pointer border p-1 rounded">
                                            <img class="lazyload mw-100 size-50px mx-auto"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7 col-lg-6">
                        <div class="text-left">
                            <h1 class="mb-2 fs-20 fw-600">
                                {{ $detailedProduct->name }}
                            </h1>


                            <div class="row align-items-center">
                                <div class="col-auto">

                                    @if ($detailedProduct->added_by == 'seller' && get_setting('vendor_system_activation') == 1)
                                        <a href="{{ route('shop.visit', $detailedProduct->user->username) }}"
                                            class="text-reset">{{ $detailedProduct->user->username }}</a>
                                    @else
                                        {{ translate('Inhouse product') }}
                                    @endif
                                </div>
                                @if (get_setting('conversation_system') == 1)
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-soft-primary"
                                            onclick="show_chat_modal()">{{ translate('Message Seller') }}</button>
                                    </div>
                                @endif

                            </div>

                            <hr>

                            @if ($detailedProduct->brand != null)
                                <div class="row align-items-center">
                                    <div class="col-2">
                                        <div class="opacity-50 my-2">{{ translate('Brand') }}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="fs-16 opacity-60">
                                            {{ $detailedProduct->brand->name }}
                                        </div>
                                    </div>
                                </div>

                                <hr>
                            @endif


                                @if (home_price($detailedProduct) != home_discounted_price($detailedProduct))
                                    <div class="row no-gutters mt-3">
                                        <div class="col-2">
                                            <div class="opacity-50 my-2">{{ translate('Price') }}:</div>
                                        </div>
                                        <div class="col-10">
                                            <div class="fs-20 opacity-60">
                                                <del>
                                                    {{ home_price($detailedProduct) }}
                                                </del>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row no-gutters my-2">
                                        <div class="col-2">
                                            <div class="opacity-50">{{ translate('Discount Price') }}:</div>
                                        </div>
                                        <div class="col-10">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ home_discounted_price($detailedProduct) }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="row no-gutters mt-3">
                                        <div class="col-2">
                                            <div class="opacity-50 my-2">{{ translate('Price') }}:</div>
                                        </div>
                                        <div class="col-10">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ home_discounted_price($detailedProduct) }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                                <div class="row no-gutters mt-4">
                                    <div class="col-2">
                                        <div class="opacity-50 my-2">{{ translate('Club Point') }}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div
                                            class="d-inline-block rounded px-2 bg-soft-primary border-soft-primary border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr>

                            <form id="option-choice-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $detailedProduct->id }}">

                                @if ($detailedProduct->choice_options != null)
                                    @foreach (json_decode($detailedProduct->choice_options) as $key => $choice)
                                        <div class="row no-gutters">
                                            <div class="col-2">
                                                <div class="opacity-50 my-2">
                                                    {{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:
                                                </div>
                                            </div>
                                            <div class="col-10">
                                                <div class="aiz-radio-inline">
                                                    @foreach ($choice->values as $key => $value)
                                                        <label class="aiz-megabox pl-0 mr-2">
                                                            <input type="radio"
                                                                name="attribute_id_{{ $choice->attribute_id }}"
                                                                value="{{ $value }}"
                                                                @if ($key == 0) checked @endif>
                                                            <span
                                                                class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                                                {{ $value }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                @if ($detailedProduct->colors!=null)
                                    <div class="row no-gutters">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ translate('Color') }}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="aiz-radio-inline">

                                                    <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip"
                                                        data-title="{{ \App\Models\Color::where('code', $detailedProduct->colors)->first()->name }}">
                                                        <input type="radio" name="color"
                                                            value="{{ \App\Models\Color::where('code', $detailedProduct->colors)->first()->name }}"
                                                            @if ($key == 0) checked @endif>
                                                        <span
                                                            class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                            <span class="size-30px d-inline-block rounded"
                                                                style="background: {{ $detailedProduct->colors }};"></span>
                                                        </span>
                                                    </label>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                @endif

                                <!-- Quantity + Add to cart -->

                                <input type="hidden" name="quantity" value="1" max="1">


                            </form>

                            <div class="mt-3">
                                @php
                                if (auth()->user() != null) {
                                    $user_id = Auth::user()->id;
                                    $cart = \App\Models\Cart::where('user_id', $user_id);
                                } else {
                                    $temp_user_id = Session()->get('temp_user_id');
                                    if ($temp_user_id) {
                                        $cart = \App\Models\Cart::where('temp_user_id', $temp_user_id);
                                    }else{
                                        $cart = null;
                                    }
                                }
                                @endphp
                                @if ($cart!=null && in_array($detailedProduct->id, $cart->pluck('product_id')->toArray()))
                                    <button type="button" class="btn btn-danger mr-2 remove-from-cart fw-600"
                                        onclick="removeFromCart( {{ $cart->where('product_id', $detailedProduct->id)->first()->id }})">
                                        <i class="las la-trash"></i>
                                        <span class="d-none d-md-inline-block"> {{ translate('Remove from cart') }}</span>
                                    </button>
                                @elseif($detailedProduct->current_stock>0)
                                    @auth
                                       <button type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600"
                                        onclick="show_offer_modal()">
                                            <i class="las la-gavel"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Offer') }}</span>
                                        </button>
                                    @endauth

                                    <button type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600"
                                        onclick="addToCart()">
                                        <i class="las la-shopping-bag"></i>
                                        <span class="d-none d-md-inline-block"> {{ translate('Add to cart') }}</span>
                                    </button>
                                    <button type="button" class="btn btn-primary buy-now fw-600" onclick="buyNow()">
                                        <i class="la la-shopping-cart"></i> {{ translate('Buy Now') }}
                                    </button>
                                @else
                                <button type="button" class="btn btn-secondary out-of-stock fw-600 d-none" disabled>
                                    <i class="la la-cart-arrow-down"></i> {{ translate('Out of Stock') }}
                                </button>
                                @endif
                            </div>



                            <div class="d-table width-100 mt-3">
                                <div class="d-table-cell">
                                    <!-- Add to wishlist button -->
                                    <button type="button" class="btn pl-0 btn-link fw-600"
                                        onclick="addToWishList({{ $detailedProduct->id }})">
                                        {{ translate('Add to wishlist') }}
                                    </button>
                                    <!-- Add to compare button -->
                                    {{-- <button type="button" class="btn btn-link btn-icon-left fw-600"
                                        onclick="addToCompare({{ $detailedProduct->id }})">
                                        {{ translate('Add to compare') }}
                                    </button> --}}
                                    @if (Auth::check() && addon_is_activated('affiliate_system') && (\App\Models\AffiliateOption::where('type', 'product_sharing')->first()->status || \App\Models\AffiliateOption::where('type', 'category_wise_affiliate')->first()->status) && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                                        @php
                                            if (Auth::check()) {
                                                if (Auth::user()->referral_code == null) {
                                                    Auth::user()->referral_code = substr(Auth::user()->id . Str::random(10), 0, 10);
                                                    Auth::user()->save();
                                                }
                                                $referral_code = Auth::user()->referral_code;
                                                $referral_code_url = URL::to('/product') . '/' . $detailedProduct->slug . "?product_referral_code=$referral_code";
                                            }
                                        @endphp
                                        <div>
                                            <button type=button id="ref-cpurl-btn" class="btn btn-sm btn-secondary"
                                                data-attrcpy="{{ translate('Copied') }}"
                                                onclick="CopyToClipboard(this)"
                                                data-url="{{ $referral_code_url }}">{{ translate('Copy the Promote Link') }}</button>
                                        </div>
                                    @endif
                                </div>
                            </div>


                            @php
                                $refund_sticker = get_setting('refund_sticker');
                            @endphp
                            @if (addon_is_activated('refund_request'))
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ translate('Refund') }}:</div>
                                    </div>
                                    <div class="col-10">
                                        <a href="{{ route('returnpolicy') }}" target="_blank">
                                            @if ($refund_sticker != null)
                                                <img src="{{ uploaded_asset($refund_sticker) }}" height="36">
                                            @else
                                                <img src="{{ static_asset('assets/img/refund-sticker.jpg') }}"
                                                    height="36">
                                            @endif
                                        </a>
                                        <a href="{{ route('returnpolicy') }}" class="ml-2"
                                            target="_blank">{{ translate('View Policy') }}</a>
                                    </div>
                                </div>
                            @endif
                            <div class="row no-gutters mt-4">
                                <div class="col-sm-2">
                                    <div class="opacity-50 my-2">{{ translate('Share') }}:</div>
                                </div>
                                <div class="col-sm-10">
                                    <div class="aiz-share"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                <div class="col-xl-3 order-1 order-xl-0">
                    @if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->shop != null)
                        <div class="bg-white shadow-sm mb-3">
                            <div class="position-relative p-3 text-left">

                                <div class="opacity-50 fs-12 border-bottom">{{ translate('Sold by') }}</div>
                                <a href="{{ route('shop.visit', $detailedProduct->user->username) }}"
                                    class="text-reset d-block fw-600">
                                    {{ $detailedProduct->user->username }}
                                </a>
                                <div class="text-center border rounded p-2 mt-3">
                                    <div class="rating">
                                        @if ($detailedProduct->user->shop->num_of_reviews > 0)
                                            {{ renderStarRating($detailedProduct->user->shop->rating) }}
                                        @else
                                            {{ renderStarRating(0) }}
                                        @endif
                                    </div>
                                    <div class="opacity-60 fs-12">({{ $detailedProduct->user->shop->num_of_reviews  }}
                                        {{ translate('customer reviews') }})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->username) }}"
                                        class="d-block btn btn-soft-primary rounded-0">{{ translate('Visit Store') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-xl-9 order-0 order-xl-1">
                    <div class="bg-white mb-3 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab"
                                class="p-3 fs-16 fw-600 text-reset active show">{{ translate('Description') }}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                        <?php echo $detailedProduct->description; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded shadow-sm">
                        <div class="border-bottom p-3">
                            <h3 class="fs-16 fw-600 mb-0">
                                <span class="mr-4">{{ translate('Related products') }}</span>
                            </h3>
                        </div>
                        <div class="p-3">
                            <div class="aiz-carousel gutters-5 half-outside-arrow" data-items="5" data-xl-items="3"
                                data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2"
                                data-arrows='true' data-infinite='true'>
                                @foreach (filter_products(\App\Models\Product::where('category_id', $detailedProduct->category_id)->where('id', '!=', $detailedProduct->id))->limit(10)->get() as $key => $related_product)
                                    <div class="carousel-box">
                                        <div
                                            class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                            <div class="">
                                                <a href="{{ route('product', $related_product->slug) }}"
                                                    class="d-block">
                                                    <img class="img-fit lazyload mx-auto h-140px h-md-210px"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($related_product->thumbnail_img) }}"
                                                        alt="{{ $related_product->name }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                </a>
                                            </div>
                                            <div class="p-md-3 p-2 text-left">
                                                <div class="fs-15">
                                                    @if (home_base_price($related_product->unit_price) != home_discounted_base_price($related_product))
                                                        <del
                                                            class="fw-600 opacity-50 mr-1">{{ home_base_price($related_product->unit_price) }}</del>
                                                    @endif
                                                    <span
                                                        class="fw-700 text-primary">{{ home_discounted_base_price($related_product) }}</span>
                                                </div>
                                                <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
                                                    <a href="{{ route('product', $related_product->slug) }}"
                                                        class="d-block text-reset">{{ $related_product->name }}</a>
                                                </h3>
                                                @if (addon_is_activated('club_point'))
                                                    <div
                                                        class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                                        {{ translate('Club Point') }}:
                                                        <span
                                                            class="fw-700 float-right">{{ $related_product->earn_point }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    {{-- Product Query --}}
                    @if(get_setting('product_query_activation') == 1)
                        <div class="bg-white rounded shadow-sm mt-3">
                            <div class="border-bottom p-3">
                                <h3 class="fs-18 fw-600 mb-0">
                                    <span>{{ translate(' Product Queries ') }} ({{ $total_query }})</span>
                                </h3>
                            </div>
                            @guest
                                <p class="fs-14 fw-400 mb-0 ml-3 mt-2"><a
                                        href="{{ route('user.login') }}">{{ translate('Login') }}</a> or <a class="mr-1"
                                        href="{{ route('user.registration') }}">{{ translate('Register ') }}</a>{{ translate(' to submit your questions to seller') }}
                                </p>
                            @endguest
                            @auth
                                <div class="query form p-3">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form action="{{ route('product-queries.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product" value="{{ $detailedProduct->id }}">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="3" cols="40" name="question"
                                                placeholder="{{ translate('Write your question here...') }}" style="resize: none;"></textarea>

                                        </div>
                                        <button type="submit" class="btn btn-primary">{{ translate('Submit') }}</button>
                                    </form>
                                </div>
                                @php
                                    $own_product_queries = Auth::user()->product_queries->where('product_id',$detailedProduct->id);
                                @endphp
                                @if ($own_product_queries->count() > 0)

                                    <div class="question-area my-4   mb-0 ml-3">

                                        <div class="border-bottom py-3">
                                            <h3 class="fs-18 fw-600 mb-0">
                                                <span class="mr-4">{{ translate('My Questions') }}</span>
                                            </h3>
                                        </div>
                                        @foreach ($own_product_queries as $product_query)
                                            <div class="produc-queries border-bottom">
                                                <div class="query d-flex my-4">
                                                    <span class="mt-1"><svg xmlns="http://www.w3.org/2000/svg" width="24.994"
                                                            height="24.981" viewBox="0 0 24.994 24.981">
                                                            <g id="Group_23909" data-name="Group 23909"
                                                                transform="translate(18392.496 11044.037)">
                                                                <path id="Subtraction_90" data-name="Subtraction 90"
                                                                    d="M1830.569-117.742a.4.4,0,0,1-.158-.035.423.423,0,0,1-.252-.446c0-.84,0-1.692,0-2.516v-2.2a5.481,5.481,0,0,1-2.391-.745,5.331,5.331,0,0,1-2.749-4.711c-.034-2.365-.018-4.769,0-7.094l0-.649a5.539,5.539,0,0,1,4.694-5.513,5.842,5.842,0,0,1,.921-.065q3.865,0,7.73,0l5.035,0a5.539,5.539,0,0,1,5.591,5.57c.01,2.577.01,5.166,0,7.693a5.54,5.54,0,0,1-4.842,5.506,6.5,6.5,0,0,1-.823.046l-3.225,0c-1.454,0-2.753,0-3.97,0a.555.555,0,0,0-.435.182c-1.205,1.214-2.435,2.445-3.623,3.636l-.062.062-1.005,1.007-.037.037-.069.069A.464.464,0,0,1,1830.569-117.742Zm7.37-11.235h0l1.914,1.521.817-.754-1.621-1.273a3.517,3.517,0,0,0,1.172-1.487,5.633,5.633,0,0,0,.418-2.267v-.58a5.629,5.629,0,0,0-.448-2.323,3.443,3.443,0,0,0-1.282-1.525,3.538,3.538,0,0,0-1.93-.53,3.473,3.473,0,0,0-1.905.534,3.482,3.482,0,0,0-1.288,1.537,5.582,5.582,0,0,0-.454,2.314v.654a5.405,5.405,0,0,0,.471,2.261,3.492,3.492,0,0,0,1.287,1.5,3.492,3.492,0,0,0,1.9.527,3.911,3.911,0,0,0,.947-.112Zm-.948-.9a2.122,2.122,0,0,1-1.812-.9,4.125,4.125,0,0,1-.652-2.457v-.667a4.008,4.008,0,0,1,.671-2.4,2.118,2.118,0,0,1,1.78-.863,2.138,2.138,0,0,1,1.824.869,4.145,4.145,0,0,1,.639,2.473v.673a4.07,4.07,0,0,1-.655,2.423A2.125,2.125,0,0,1,1836.991-129.881Z"
                                                                    transform="translate(-20217 -10901.814)" fill="#e62e04"
                                                                    stroke="rgba(0,0,0,0)" stroke-miterlimit="10"
                                                                    stroke-width="1" />
                                                            </g>
                                                        </svg></span>

                                                    <div class="ml-3">
                                                        <div class="fs-14">{{ strip_tags($product_query->question) }}</div>
                                                        <span class="text-secondary">{{ $product_query->user->name }} </span>
                                                    </div>
                                                </div>
                                                <div class="answer d-flex my-4">
                                                    <span class="mt-1"> <svg xmlns="http://www.w3.org/2000/svg" width="24.99"
                                                            height="24.98" viewBox="0 0 24.99 24.98">
                                                            <g id="Group_23908" data-name="Group 23908"
                                                                transform="translate(17952.169 11072.5)">
                                                                <path id="Subtraction_89" data-name="Subtraction 89"
                                                                    d="M2162.9-146.2a.4.4,0,0,1-.159-.035.423.423,0,0,1-.251-.446q0-.979,0-1.958V-151.4a5.478,5.478,0,0,1-2.39-.744,5.335,5.335,0,0,1-2.75-4.712c-.034-2.355-.018-4.75,0-7.065l0-.678a5.54,5.54,0,0,1,4.7-5.513,5.639,5.639,0,0,1,.92-.064c2.527,0,5.029,0,7.437,0l5.329,0a5.538,5.538,0,0,1,5.591,5.57c.01,2.708.01,5.224,0,7.692a5.539,5.539,0,0,1-4.843,5.506,6,6,0,0,1-.822.046l-3.234,0c-1.358,0-2.691,0-3.96,0a.556.556,0,0,0-.436.182c-1.173,1.182-2.357,2.367-3.5,3.514l-1.189,1.192-.047.048-.058.059A.462.462,0,0,1,2162.9-146.2Zm5.115-12.835h3.559l.812,2.223h1.149l-3.25-8.494h-.98l-3.244,8.494h1.155l.8-2.222Zm3.226-.915h-2.888l1.441-3.974,1.447,3.972Z"
                                                                    transform="translate(-20109 -10901.815)" fill="#f7941d"
                                                                    stroke="rgba(0,0,0,0)" stroke-miterlimit="10"
                                                                    stroke-width="1" />
                                                            </g>
                                                        </svg></span>

                                                    <div class="ml-3">
                                                        <div class="fs-14">
                                                            {{ strip_tags($product_query->reply ? $product_query->reply : translate('Seller did not respond yet')) }}
                                                        </div>
                                                        <span class=" text-secondary">
                                                            {{ $product_query->product->user->name }} </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                @endif
                            @endauth

                            <div class="pagination-area my-4 mb-0 ml-3">
                                @include('frontend.partials.product_query_pagination')
                            </div>
                        </div>
                    @endif
                    {{-- End of Product Query --}}
                </div>
            </div>
        </div>
    </section>

@endsection

@section('modal')

    <div class="modal fade" id="offer_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('Give an offer') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label>{{ translate('Amount')}} <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control mb-3" id="offer_amount" name="offer_amount" placeholder="{{ translate('Amount')}}" min="0" required>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" id="offer_create" class="btn btn-sm btn-primary transition-3d-hover mr-1">{{translate('Confirm')}}</button>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ translate('Any query about this product') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title"
                                value="{{ $detailedProduct->name }}" placeholder="{{ translate('Product Name') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required
                                placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ translate('Login') }}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}"
                            method="POST">
                            @csrf
                            <div class="form-group">
                                @if (addon_is_activated('otp_system'))
                                    <input type="text"
                                        class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone') }}"
                                        name="email" id="email">
                                @else
                                    <input type="email"
                                        class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}" placeholder="{{ translate('Email') }}"
                                        name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{ translate('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg"
                                    placeholder="{{ translate('Password') }}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{ translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}"
                                        class="text-reset opacity-60 fs-14">{{ translate('Forgot password?') }}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit"
                                    class="btn btn-primary btn-block fw-600">{{ translate('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ translate('Dont have an account?') }}</p>
                            <a href="{{ route('user.registration') }}">{{ translate('Register Now') }}</a>
                        </div>
                        @if (get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ translate('Or Login With') }}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}"
                                            class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('google_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'google']) }}"
                                            class="google">
                                            <i class="lab la-google"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('twitter_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'twitter']) }}"
                                            class="twitter">
                                            <i class="lab la-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">


        function CopyToClipboard(e) {
            var url = $(e).data('url');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(url).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ translate('Link copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ translate('Oops, unable to copy') }}');
            }
            $temp.remove();
            // if (document.selection) {
            //     var range = document.body.createTextRange();
            //     range.moveToElementText(document.getElementById(containerid));
            //     range.select().createTextRange();
            //     document.execCommand("Copy");

            // } else if (window.getSelection) {
            //     var range = document.createRange();
            //     document.getElementById(containerid).style.display = "block";
            //     range.selectNode(document.getElementById(containerid));
            //     window.getSelection().addRange(range);
            //     document.execCommand("Copy");
            //     document.getElementById(containerid).style.display = "none";

            // }
            // AIZ.plugins.notify('success', 'Copied');
        }

        function show_chat_modal() {
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        // Pagination using ajax
        $(window).on('hashchange', function() {
            if (window.location.hash) {
                var page = window.location.hash.replace('#', '');
                if (page == Number.NaN || page <= 0) {
                    return false;
                } else {
                    getQuestions(page);
                }
            }
        });

        function show_offer_modal(){
            $('#offer_modal').modal('show');
        }

        $(document).ready(function() {
            $(document).on('click', '.pagination a', function(e) {
                getQuestions($(this).attr('href').split('page=')[1]);
                e.preventDefault();
            });

            $(document).on('click', '.remove-from-cart', function(e) {
                location.reload();
            });

            $(document).on('click', '#offer_create', function(e) {

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type:"POST",
                    url:'{{ route('offer.create') }}',
                    data:{
                        product_id: {{$detailedProduct->id}},
                        offer_value: $('#offer_amount').val(),
                    },
                    success: function(data) {
                        if(data.status){
                            AIZ.plugins.notify('success', data.message);
                        }else{
                            AIZ.plugins.notify('warning', data.message);
                        }
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                });
            });
        });

        function getQuestions(page) {
            $.ajax({
                url: '?page=' + page,
                dataType: 'json',
            }).done(function(data) {
                $('.pagination-area').html(data);
                location.hash = page;
            }).fail(function() {
                alert('Something went worng! Questions could not be loaded.');
            });
        }
        // Pagination end
    </script>
@endsection
