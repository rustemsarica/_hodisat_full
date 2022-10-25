<div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-2 has-transition bg-white">
    @if(discount_in_percentage($product) > 0)
        <span class="badge-custom">{{ translate('OFF') }}<span class="box ml-1 mr-0">&nbsp;{{discount_in_percentage($product)}}%</span></span>
    @endif
    <div class="position-relative">
        @php
            $product_url = route('product', $product->slug);
            if($product->auction_product == 1) {
                $product_url = route('auction-product', $product->slug);
            }
        @endphp
        <a href="{{ $product_url }}" class="d-block">
            <img
                class="img-fit lazyload mx-auto h-140px h-md-210px"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                alt="{{  $product->getTranslation('name')  }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>
        @if ($product->current_stock==0)
        <span class="absolute-center text-center fs-20 text-white fw-600 p-2 lh-1-8" style="background-color: #455a64; opacity:0.7; width:100%;">
            {{ translate('Sold') }}
        </span>
        @endif
    </div>
    <div class="p-md-3 p-2 text-left">
        <div class="d-flex justify-content-between">
            <div class="fs-18">
                @if(home_base_price($product->unit_price) != home_discounted_base_price($product))
                    <del class="fw-600 opacity-80 mr-1">{{ home_base_price($product->unit_price) }}</del>
                @endif
                <span class="fw-700 text-primary">{{ home_discounted_base_price($product) }}</span>
            </div>
            <div class="c-pointer fs-24" onclick="addToWishList({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                <i class="la la-heart-o"></i>
            </div>
        </div>
    </div>
</div>
