@php
    $blogs = Cache::remember('blogs', 86400, function () {
        return \App\Models\Blog::where('status', 1)->latest()->limit(10)->get();
    });
@endphp

<div class="aiz-carousel gutters-5 mb-4" data-items="1" data-xs-items="2" data-rows="5" data-xs-rows="1" data-arrows='false'>
    @foreach ($blogs as $key => $blog)
        <div class="carousel-box">
            <div class="row no-gutters box-3 align-items-center border border-light rounded hov-shadow-md my-2 has-transition">
                <div class="col-12 col-md-4">
                    <a href="{{ url('blog').'/'. $blog->slug }}" class="d-block p-3">
                        <img
                            style="background-color: whitesmoke; object-fit:cover; width:100%; height:100px;" src="@if ($blog->banner !== null) {{ uploaded_asset($blog->banner) }} @else {{ static_asset('assets/img/placeholder.jpg') }} @endif"
                            width="100"
                            height="100"
                            alt="{{ $blog->slug }}"
                            class="img-fluid lazyload"
                        >
                    </a>
                </div>
                <div class="col-12 col-md-8 border-left border-light">
                    <div class="p-3 text-left">

                        <a href="{{ url('blog').'/'. $blog->slug }}" class="text-reset fs-12 fw-600">{{ $blog->title }}</a>

                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

