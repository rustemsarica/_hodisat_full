@php
    $best_selers = Cache::remember('best_selers', 86400, function () {
        return \App\Models\Seller::orderBy('num_of_sale', 'desc')->take(6)->get();
    });
@endphp

@if (get_setting('vendor_system_activation') == 1)
<div class="aiz-carousel  half-outside-arrow gutters-5 mb-4" data-items="1" data-xs-items="2" data-rows="5" data-xs-rows="1" data-arrows='false' data-xs-arrows='true'>
    @foreach ($best_selers as $key => $seller)
        @if ($seller->user != null)
            <div class="carousel-box">
                <div class="row no-gutters box-3 align-items-center border border-light rounded hov-shadow-md my-2 has-transition">
                    <div class="col-12 col-md-5">
                        <a href="{{ route('shop.visit', $seller->user->username) }}" class="d-block p-3">
                            <img
                                style="background-color: whitesmoke; object-fit:cover; width:100%; height:100px;" src=" {{ static_asset('assets/img/avatar-place.png') }} "
                                alt="{{ $seller->user->username }}"
                                width="100"
                                height="100"
                                class="img-fluid lazyload"
                            >
                        </a>
                    </div>
                    <div class="col-12 col-md-7 border-left border-light">
                        <div class="p-3 text-left">
                            <a href="{{ route('shop.visit', $seller->user->username) }}" class="text-reset h6 fw-600 text-truncate">{{ $seller->user->username }}</a>
                            <div class="rating rating-sm mb-2">
                                {{ renderStarRating($seller->rating) }}
                            </div>
                            <a href="{{ route('shop.visit', $seller->user->username) }}" class="btn btn-outline-primary btn-sm">
                                {{ translate('Visit Store') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

@endif
