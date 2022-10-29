@if(get_setting('topbar_banner') != null)
<div class="position-relative top-banner removable-session z-1035 d-none" data-key="top-banner" data-value="removed">
    <a href="{{ get_setting('topbar_banner_link') }}" class="d-block text-reset">
        <img src="{{ uploaded_asset(get_setting('topbar_banner')) }}" class="w-100 mw-100 h-50px h-lg-auto img-fit">
    </a>
    <button class="btn text-white absolute-top-right set-session" data-key="top-banner" data-value="removed" data-toggle="remove-parent" data-parent=".top-banner">
        <i class="la la-close la-2x"></i>
    </button>
</div>
@endif
<!-- Top Bar -->
<div class="top-navbar bg-white border-bottom border-soft-secondary z-1035 h-35px h-sm-auto">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col">
                <ul class="list-inline d-flex justify-content-between justify-content-lg-start mb-0">
                    @if(get_setting('show_language_switcher') == 'on')
                    <li class="list-inline-item dropdown mr-3" id="lang-change">
                        @php
                            if(Session::has('locale')){
                                $locale = Session::get('locale', Config::get('app.locale'));
                            }
                            else{
                                $locale = 'en';
                            }
                        @endphp
                        <div class="dropdown-toggle text-reset py-2 c-pointer" data-toggle="dropdown" data-display="static">
                            <img src="{{ static_asset('assets/img/flags/tr.png') }}" data-src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" class="mr-2 lazyload" alt="{{ \App\Models\Language::where('code', $locale)->first()->name }}" height="11" width="16">
                            <span class="opacity-80">{{ \App\Models\Language::where('code', $locale)->first()->name }}</span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-left">
                            @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                                <li>
                                    <div data-flag="{{ $language->code }}" class="dropdown-item c-pointer @if($locale == $language) active @endif">
                                        <img style="background-color: whitesmoke;" src="{{ static_asset('assets/img/flags/tr.png') }}" data-src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-1 lazyload" alt="{{ $language->name }}" height="11" width="16">
                                        <span class="language">{{ $language->name }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif

                    @if(get_setting('show_currency_switcher') == 'on')
                        <li class="list-inline-item dropdown ml-auto ml-lg-0 mr-0" id="currency-change">
                            @php
                                if(Session::has('currency_code')){
                                    $currency_code = Session::get('currency_code');
                                }
                                else{
                                    $currency_code = \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code;
                                }
                            @endphp
                            <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2 opacity-80" data-toggle="dropdown" data-display="static">
                                {{ \App\Models\Currency::where('code', $currency_code)->first()->name }} {{ (\App\Models\Currency::where('code', $currency_code)->first()->symbol) }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left">
                                @foreach (\App\Models\Currency::where('status', 1)->get() as $key => $currency)
                                    <li>
                                        <a class="dropdown-item @if($currency_code == $currency->code) active @endif" href="javascript:void(0)" data-currency="{{ $currency->code }}">{{ $currency->name }} ({{ $currency->symbol }})</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="col-5 text-right d-none d-lg-block">
                <ul class="list-inline mb-0 h-100 d-flex justify-content-end align-items-center">
                    @if (get_setting('contact_email'))
                        <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="mailto:{{ get_setting('contact_email') }}" class="text-reset d-inline-block opacity-80 py-2">
                                <i class="las la-mail-bulk"></i>
                                <span>{{ get_setting('contact_email') }}</span>
                            </a>
                        </li>
                    @endif
                    @auth
                        @if(isAdmin())
                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                <a href="{{ route('admin.dashboard') }}" class="text-reset d-inline-block opacity-80 py-2">{{ translate('My Panel')}}</a>
                            </li>
                        @else

                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0 dropdown">
                                <div class="dropdown-toggle no-arrow text-reset c-pointer" data-toggle="dropdown">
                                    <span class="">
                                        <span class="position-relative d-inline-block">
                                            <i class="las la-bell fs-18"></i>
                                            @if(count(Auth::user()->unreadNotifications) > 0)
                                                <span class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                            @endif
                                        </span>
                                    </span>
                                </div>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg py-0">
                                    <div class="p-3 bg-light border-bottom">
                                        <h6 class="mb-0">{{ translate('Notifications') }}</h6>
                                    </div>
                                    <div class="px-3 c-scrollbar-light overflow-auto " style="max-height:300px;">
                                        <ul class="list-group list-group-flush" >
                                            @forelse(Auth::user()->unreadNotifications as $notification)
                                                <li class="list-group-item">
                                                    @if($notification->type == 'App\Notifications\OrderNotification')
                                                        @if (Auth::user()->user_type == 'seller')
                                                            <a href="{{ route('seller.orders.show', encrypt($notification->data['order_id'])) }}" class="text-reset">
                                                                <span class="ml-2">
                                                                    {{translate('Order code: ')}} {{$notification->data['order_code']}} {{ translate('has been '. ucfirst(str_replace('_', ' ', $notification->data['status'])))}}
                                                                </span>
                                                            </a>
                                                        @endif
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="list-group-item">
                                                    <div class="py-4 text-center fs-16">
                                                        {{ translate('No notification found') }}
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div class="text-center border-top">
                                        <a href="{{ route('all-notifications') }}" class="text-reset d-block py-2">
                                            {{translate('View All Notifications')}}
                                        </a>
                                    </div>
                                </div>
                            </li>

                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                @if (Auth::user()->user_type == 'seller')
                                    <a href="{{ route('seller.dashboard') }}" class="text-reset d-inline-block opacity-80 py-2">{{ translate('My Panel')}}</a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="text-reset d-inline-block opacity-80 py-2">{{ translate('My Panel')}}</a>
                                @endif
                            </li>
                        @endif
                        <li class="list-inline-item">
                            <a href="{{ route('logout') }}" class="text-reset d-inline-block opacity-80 py-2">{{ translate('Logout')}}</a>
                        </li>
                    @else
                        <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="{{ route('user.login') }}" class="text-reset d-inline-block opacity-80 py-2">{{ translate('Login')}}</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ route('user.registration') }}" class="text-reset d-inline-block opacity-80 py-2">{{ translate('Registration')}}</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- END Top Bar -->
<header class="@if(get_setting('header_stikcy') == 'on') sticky-top @endif z-1020 bg-white border-bottom shadow-sm">
    <div class="position-relative logo-bar-area z-1">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">

                <div class="col-xl-3 pl-0 pr-3 d-flex align-items-center">
                    <a class="d-block py-10px " href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-50px" height="60" width="219" style="width: 100%" >
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-30px h-md-60px" height="50" width="160">
                        @endif
                    </a>
                </div>

                <div class="d-lg-none ml-auto mr-0">
                    <div class="p-2 d-block text-reset c-pointer" data-toggle="class-toggle" data-target=".front-header-search">
                        <i class="las la-search la-flip-horizontal la-2x"></i>
                    </div>
                </div>

                <div class="col-xl-6 flex-grow-1 front-header-search d-flex align-items-center bg-white">
                    <div class="position-relative flex-grow-1 m-auto" style="max-width: 500px">
                        <form action="{{ route('search') }}" method="GET" class="stop-propagation" id="searcForm">
                            <div class="d-flex position-relative align-items-center">
                                <div class="d-lg-none" data-toggle="class-toggle" data-target=".front-header-search">
                                    <button class="btn px-2" type="button"><i class="la la-2x la-long-arrow-left"></i></button>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="border-0 border-lg form-control" id="search" name="keyword" @isset($query)
                                        value="{{ $query }}"
                                    @endisset placeholder="{{translate('I am shopping for...')}}" autocomplete="off">
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

                <div class="d-none d-lg-none ml-3 mr-0">
                    <div class="nav-search-box">
                        <a href="#" class="nav-box-link">
                            <i class="la la-search la-flip-horizontal d-inline-block nav-box-icon"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 d-none d-lg-flex mr-3 justify-content-end">
                    <div class="ml-3 mr-0">
                        <a href="{{ route('wishlists.index') }}" class="d-flex align-items-center text-reset">
                            <i class="la la-heart-o la-2x opacity-80"></i>
                            <span class="flex-grow-1 ml-1">
                                @if(Auth::check())
                                    <span class="badge badge-primary badge-inline badge-pill">{{ count(Auth::user()->wishlists)}}</span>
                                @else
                                    <span class="badge badge-primary badge-inline badge-pill">0</span>
                                @endif
                                <span class="nav-box-text d-none d-xl-block opacity-70">{{translate('Wishlist')}}</span>
                            </span>
                        </a>
                    </div>

                    <div class="align-self-stretch ml-3 mr-0" data-hover="dropdown">
                        <div class="nav-cart-box dropdown h-100" id="cart_items">
                            @include('frontend.partials.cart')
                        </div>
                    </div>

                    <div class="ml-3 mr-0">
                        @if(Auth::check())
                            <a href="{{ route('seller.dashboard') }}" class="d-flex align-items-center text-reset">
                                <i class="la la-user-o la-2x opacity-80"></i>
                                <span class="flex-grow-1 ml-1">
                                    <span class="badge badge-inline badge-pill text-white">0</span>
                                    <span class="nav-box-text d-none d-xl-block opacity-70">{{ translate('My Panel')}}</span>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('user.login') }}" class="d-flex align-items-center text-reset">
                                <i class="la la-user-o la-2x opacity-80"></i>
                                <span class="flex-grow-1 ml-1">
                                    <span class="badge badge-inline badge-pill text-white">0</span>
                                    <span class="nav-box-text d-none d-xl-block opacity-70">{{translate('Login')}}</span>
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
    @if ( get_setting('header_menu_labels') !=  null )
        <div class="bg-white border-top border-gray-200 py-1">
            <div class="container">
                <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-center">
                    @foreach (json_decode( get_setting('header_menu_labels'), true) as $key => $value)
                    <li class="list-inline-item mr-0">
                        <a href="{{ json_decode( get_setting('header_menu_links'), true)[$key] }}" class="opacity-80 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                            {{ translate($value) }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="aiz-category-menu d-none d-md-inline bg-white border-top border-gray-200 py-1">
        <div class="container">
            <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-center">
                @php
                    $categories=getHeaderCategories();
                @endphp
                @foreach ($categories as $value)
                <li class="list-inline-item mr-0 category-nav-element"  data-id="{{ $value->id }}">
                    <a href="{{ route('products.category', $value->slug) }}" class="opacity-80 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                        {{ $value->getTranslation('name') }}
                    </a>

                        <div class="sub-cat-menu c-scrollbar-light rounded shadow-lg p-4">
                            <div class="c-preloader text-center absolute-center">
                                <i class="las la-spinner la-spin la-3x opacity-70"></i>
                            </div>
                        </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</header>

<div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div id="order-details-modal-body">

            </div>
        </div>
    </div>
</div>

@section('script')
    <script type="text/javascript">

        function show_order_details(order_id)
        {
            $('#order-details-modal-body').html(null);

            if(!$('#modal-size').hasClass('modal-lg')){
                $('#modal-size').addClass('modal-lg');
            }

            $.post('{{ route('admin.orders.details') }}', { _token : AIZ.data.csrf, order_id : order_id}, function(data){
                $('#order-details-modal-body').html(data);
                $('#order_details').modal();
                $('.c-preloader').hide();
                AIZ.plugins.bootstrapSelect('refresh');
            });
        }
    </script>
@endsection
