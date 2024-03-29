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
    <div class="px-md-3 px-2 pt-2 pb-1 fs-14 text-truncate">
        {{$product->name}}
    </div>

    <div class="px-md-3 px-2 pb-1 d-flex justify-content-between">
        <div class="fs-18">
            @if(home_base_price($product->unit_price) != home_discounted_base_price($product))
                <del class="fw-600 opacity-80 mr-1">{{ home_base_price($product->unit_price) }}</del>
            @endif
            <span class="fw-700 text-primary">{{ home_discounted_base_price($product) }}</span>
        </div>
        <div class="c-pointer fs-24 d-flex justify-content-between" onclick="addToWishList({{ $product->id }})">
            @auth
                @if(App\Models\Wishlist::where(['product_id'=>$product->id, 'user_id'=>Auth::user()->id])->count()>0)
                <i class="la la-heart" style="color: var(--red)"></i>
                @else
                <i class="la la-heart-o"></i>
                @endif
            @else
            <i class="la la-heart-o"></i>
            @endauth
            <div class="pl-1 fs-16 opacity-80 m-auto">{{$product->wishlists_count}}</div>
        </div>
    </div>
    <div class="fs-14 opacity-80 px-md-3 px-2 pb-md-2 pb-2">
        {{$product->brand->name}}
    </div>
</div>
