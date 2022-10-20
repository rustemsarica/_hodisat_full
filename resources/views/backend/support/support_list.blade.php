@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_supports" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Support') }}</h5>
            </div>

            <div class="col-lg-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="parent_category" id="parent_category">
                    <option value="">{{translate('Parent Category')}}</option>
                    @foreach (\App\Models\Support::where('parent_id',0)->get() as $item)
                        <option value="{{$item->id}}" @if ($parent_category == '{{$item->id}}') selected @endif>{{$item->title}}</option>
                    @endforeach


                </select>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Search') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>Ana Kategori</th>
                        <th>Başlık</th>
                        <th>İçerik</th>
                        <th class="text-right" width="15%">{{translate('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($supports as $key => $support)
                    <tr>

                        <td>
                            {{ \App\Models\Support::where('id',$support->id)->first()->title }}
                        </td>
                        <td>
                            {{ $support->title }}
                        </td>
                        <td>
                            {{ $support->text }}
                        </td>

                        <td class="text-right">

                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('support_ticket.getSupport', $support->id) }}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            @if ($support->parent_id!=0)
                              <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('support_ticket.deleteSupport', $support->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            @endif

                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $supports->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">


    </script>
@endsection