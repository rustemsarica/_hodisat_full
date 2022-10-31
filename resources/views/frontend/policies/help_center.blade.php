@extends('frontend.layouts.app')

@section('meta_title'){{ $page->getTranslation('title') }}@stop

@section('meta_description'){{ $page->meta_description }}@stop

@section('meta_keywords'){{ $page->tags }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $page->getTranslation('title') }}">
    <meta itemprop="description" content="{{ $page->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($page->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="website">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $page->getTranslation('title') }}">
    <meta name="twitter:description" content="{{ $page->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($page->meta_img) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $page->getTranslation('title') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ URL($page->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($page->meta_img) }}" />
    <meta property="og:description" content="{{ $page->meta_description }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
@endsection
@section('content')
<style>
    .list-group > .active{
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
    }
</style>

<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ $page->getTranslation('title') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('helpcenter') }}">"{{ translate('Help Center') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

@if ($page->getTranslation('content')!=null && $page->getTranslation('content')!="")
<section class="mb-4">
    <div class="container">
        <div class="p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left">
            @php
                echo $page->getTranslation('content');
            @endphp
        </div>
    </div>
</section>
@endif


<section class="mb-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 m-auto">
                <div class="position-relative flex-grow-1 m-auto" style="max-width: 500px">
                    <form method="GET" class="stop-propagation" id="searchSupportForm">
                        <div class="d-flex position-relative align-items-center">
                            <div class="input-group">
                                <input type="text" class="border-0 border-lg form-control" id="support-search" name="support_search" @isset($support_search)
                                    value="{{ $support_search }}"
                                @endisset placeholder="{{translate('Search')}}" autocomplete="off">
                            </div>
                        </div>
                    </form>
                    <div class="typed-support-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100" style="min-height: 200px">
                        <div class="search-preloader absolute-top-center">
                            <div class="dot-loader"><div></div><div></div><div></div></div>
                        </div>
                        <div class="search-nothing d-none p-3 text-center fs-16">

                        </div>
                        <div id="support-search-content" class="text-left">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(count($supports)==0)
<section class="mb-4">
    <div class="container">
        <div class="row">
            <div class="col-4">
              <div class="list-group" id="list-tab" role="tablist">
                @foreach (\App\Models\Support::where('parent_id',0)->get() as $support)
                    <a class="list-group-item list-group-item-action p-4 fs-16" id="list-{{$support->id}}-list" data-toggle="list" href="#list-{{$support->id}}" role="tab" aria-controls="{{$support->id}}">{{$support->title}}</a>
                @endforeach

              </div>
            </div>
            <div class="col-8">
              <div class="tab-content" id="nav-tabContent">
                @foreach (\App\Models\Support::where('parent_id',0)->get() as $support)
                    <div class="tab-pane fade " id="list-{{$support->id}}" role="tabpanel" aria-labelledby="list-{{$support->id}}-list">
                        <div class="accordion" id="accordion{{$support->id}}">
                            @foreach (\App\Models\Support::where('parent_id',$support->id)->get() as $item)
                            <div class="card mb-0">
                              <div class="card-header p-0" id="heading{{$item->id}}" type="button" data-toggle="collapse" data-target="#collapse{{$item->id}}" aria-expanded="true" aria-controls="collapse{{$item->id}}">
                                <h2 class="mb-0 px-4 btn-link fs-14">
                                    {{$item->title}}
                                </h2>
                              </div>

                              <div id="collapse{{$item->id}}" class="collapse" aria-labelledby="heading{{$item->id}}" data-parent="#accordion{{$support->id}}">
                                <div class="card-body">
                                    {!!$item->text!!}
                                </div>
                              </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
              </div>
            </div>
        </div>
    </div>
</section>
@else

<section class="mb-4">
    <div class="container">
        <div class="row">
            <a class="btn btn-primary" href="{{route('helpcenter')}}"><i class="las la-undo"></i> {{translate('Help Center')}}</a>
        </div>
        <div class="row">
            <div class="col-8 m-auto">
              <div class="tab-content" id="nav-tabContent">
                <div class="accordion" id="accordion-search">
                    @foreach ($supports as $item)
                    <div class="card mb-0">
                      <div class="card-header p-0" id="heading{{$item->id}}" type="button" data-toggle="collapse" data-target="#collapse{{$item->id}}" aria-expanded="true" aria-controls="collapse{{$item->id}}">
                        <h2 class="mb-0 px-4 btn-link fs-14">
                            {{$item->title}}
                        </h2>
                      </div>
                      <div id="collapse{{$item->id}}" class="collapse" aria-labelledby="heading{{$item->id}}" data-parent="#accordion-search">
                        <div class="card-body">
                            {!!$item->text!!}
                        </div>
                      </div>
                    </div>
                    @endforeach
                </div>
              </div>
            </div>
        </div>
    </div>
</section>
@endif

<section class="mb-4">
    <div class="container">
        <div class="row">
            @if ( get_setting('widget_one_labels',null,App::getLocale()) !=  null )
                @foreach (json_decode( get_setting('widget_one_labels',null,App::getLocale()), true) as $key => $value)
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <a href="{{ json_decode( get_setting('widget_one_links'), true)[$key] }}" class="opacity-80 hov-opacity-100 text-reset fs-16">
                            {{ $value }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

@endsection

@section('script')
 <script>
        $('#support-search').on('keyup', function(){
        supportSearch();
        });

        $('#support-search').on('focus', function(){
            supportSearch();
        });

        function supportSearch(){
            var searchKey = $('#support-search').val();
            if(searchKey.length > 0){
                $('body').addClass("typed-support-search-box-shown");

                $('.typed-support-search-box').removeClass('d-none');
                $('.search-preloader').removeClass('d-none');
                $.post('{{ route('search.ajax') }}', { _token: AIZ.data.csrf, support_search:searchKey}, function(data){
                    if(data == '0'){
                        // $('.typed-search-box').addClass('d-none');
                        $('#support-search-content').html(null);
                        $('.typed-support-search-box .search-nothing').removeClass('d-none').html('Sorry, nothing found for <strong>"'+searchKey+'"</strong>');
                        $('.search-preloader').addClass('d-none');

                    }
                    else{
                        $('.typed-support-search-box .search-nothing').addClass('d-none').html(null);
                        $('#support-search-content').html(data);
                        $('.search-preloader').addClass('d-none');
                    }
                });
            }
            else {
                $('.typed-support-search-box').addClass('d-none');
                $('body').removeClass("typed-support-search-box-shown");
            }
        }
 </script>
@endsection
