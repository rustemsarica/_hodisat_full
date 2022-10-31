@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('All Categories')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <span>{{translate('Add New category')}}</span>
            </a>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header d-block d-md-flex">
        <h5 class="mb-0 h6">{{ translate('Categories') }}
        @if (isset($parent))
            @if ($parent->parent_id==0)
            <a class="btn btn-sm btn-primary" href="{{route('admin.categories.index')}}">{{$parent->getTranslation('name')}}</a>
            @else
                <a class="btn btn-sm btn-primary" href="{{route('admin.categories.sub',['id'=>$parent->parent_id])}}">{{$parent->getTranslation('name')}}</a>
            @endif
        @endif
    </h5>
        <button class="btn btn-sm btn-primary confirm-reorder" href="#reorder-modal">{{translate("Reorder")}}</button>

        <form class="" id="sort_categories" action="" method="GET">
            <div class="box-inline pad-rgt pull-left">
                <div class="" style="min-width: 200px;">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                </div>
            </div>
        </form>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{translate('Name')}}</th>
                    <th data-breakpoints="lg">{{ translate('Attributes') }}</th>
                    <th data-breakpoints="lg">{{ translate('Parent Category') }}</th>
                    <th data-breakpoints="lg">{{translate('Banner')}}</th>
                    <th data-breakpoints="lg">{{translate('Icon')}}</th>
                    <th data-breakpoints="lg">{{translate('Featured')}}</th>
                    <th data-breakpoints="lg">{{translate('Status')}}</th>
                    <th width="10%" class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody class="sortable">
                @foreach($categories as $key => $category)
                    <tr>
                        <td>{{ ($key+1) + ($categories->currentPage() - 1)*$categories->perPage() }}</td>
                        <td><a href="{{route('admin.categories.sub',['id'=>$category->id])}}">{{ $category->getTranslation('name') }}</a></td>
                        <td>
                            @php
                                $attributes = \App\Models\AttributeCategory::where('category_id', $category->id)->get();
                            @endphp
                            @if (count($attributes) > 0)
                                @foreach ($attributes as $item)
                                    @php
                                        $attribute = \App\Models\Attribute::find($item->attribute_id);
                                    @endphp
                                   <span class="badge badge-inline badge-md bg-soft-dark">{{ $attribute->getTranslation('name') }}</span>
                                @endforeach
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @php
                                $parent = \App\Models\Category::where('id', $category->parent_id)->first();
                            @endphp
                            @if ($parent != null)
                                {{ $parent->getTranslation('name') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($category->banner != null)
                                <img src="{{ uploaded_asset($category->banner) }}" alt="{{translate('Banner')}}" class="h-50px">
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($category->icon != null)
                                <span class="avatar avatar-square avatar-xs">
                                    <img src="{{ uploaded_asset($category->icon) }}" alt="{{translate('icon')}}">
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="update_featured(this)" value="{{ $category->id }}" <?php if($category->featured == 1) echo "checked";?>>
                                <span></span>
                            </label>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="update_status(this)" value="{{ $category->id }}" <?php if($category->status == 1) echo "checked";?>>
                                <span></span>
                            </label>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('admin.categories.edit', ['id'=>$category->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.categories.destroy', $category->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $categories->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
    <div id="reorder-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{translate('Reorder Categories')}}</h4>
                    <button type="button" class="close reorder-close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">

                    <ul id="sortable-category" class="list-group">
                        @foreach ($categories as $category )
                            <li id="category_item_{{$category->id}}" class="list-group-item">{{$category->getTranslation('name')}}</li>
                        @endforeach
                    </ul>

                </div>
            </div>
        </div>
    </div>

@endsection


@section('script')

    <script type="text/javascript">
        function update_featured(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('admin.categories.featured') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Featured categories updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }
        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('admin.categories.status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Featured categories updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }
    </script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
     var categories_array = JSON.parse(JSON.stringify(<?= json_encode($reorders);?>));
    $(document).ready(function() {

         $('#sortable-category').sortable({
            items: 'li',
            revert: true,
            opacity: 0.5,
            start: function(evt, ui) {
                var link = ui.item.find('li');
                link.data('click-event', link.attr('onclick'));
                link.attr('onclick', '');
            },
            stop: function(evt, ui) {
                setTimeout(
                    function(){
                        var link = ui.item.find('li');
                        link.attr('onclick', link.data('click-event'));
                    },
                        200
                )
                var i;
                for (i = 0; i < categories_array.length; i++) {
                    var index = $("#category_item_" + categories_array[i].id).index();
                    if (index == null || index == undefined) {
                        index = 0;
                    }
                    categories_array[i].level = index+1;
                }

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type:"POST",
                    url:'{{ route('admin.categories.categoryReorder') }}',
                    data:{json_categories: JSON.stringify(categories_array)},
                    dataType: 'JSON',
                    success: function(res) {

                    }
                });

             }
         });

     });

    $(".confirm-reorder").click(function (e) {
        e.preventDefault();
        $("#reorder-modal").modal("show");
    });

    $(".reorder-close").click(function (e) {
        location.reload();
    });
 </script>
@endsection
