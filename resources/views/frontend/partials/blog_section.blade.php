@php
    $blogs = Cache::remember('blogs', 86400, function () {
        return \App\Models\Blog::where('status', 1)->latest()->limit(5)->get();
    });
@endphp

<div class="aiz-carousel gutters-5 " data-items="1" data-rows="5" data-arrows='false'>
    @foreach ($blogs as $key => $blog)
    <div class="carousel-box">
        <div class="row no-gutters box-3 align-items-center border border-light rounded hov-shadow-md my-2 has-transition">
            <div class="col-4">
                <a href="{{ url('blog').'/'. $blog->slug }}" class="d-block p-3">
                    <img
                        style="background-color: whitesmoke;" src="@if ($blog->banner !== null) {{ uploaded_asset($blog->banner) }} @else {{ static_asset('assets/img/placeholder.jpg') }} @endif"
                        width="75"
                        height="50"
                        alt="{{ $blog->slug }}"
                        class="img-fluid lazyload"
                    >
                </a>
            </div>
            <div class="col-8 border-left border-light">
                <div class="p-3 text-left">

                    <a href="{{ url('blog').'/'. $blog->slug }}" class="text-reset fs-12 fw-600">{{ $blog->title }}</a>

                    <a href="{{ url('blog').'/'. $blog->slug }}" class="btn btn-outline-primary btn-xs">
                        {{ translate('View More') }} <i class="las la-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

