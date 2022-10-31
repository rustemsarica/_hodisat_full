@extends('backend.layouts.app')

@section('content')

@php


@endphp

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Category Information')}}</h5>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill border-light">
                    @foreach (\App\Models\Language::all() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('admin.categories.edit', ['id'=>$category->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{$language->name}}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <form class="p-4" action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                    <input name="_method" type="hidden" value="PATCH">
    	            <input type="hidden" name="lang" value="{{ $lang }}">
                	@csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Name')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                        <div class="col-md-9">
                            <input type="text" name="name" value="{{ $category->getTranslation('name', $lang) }}" class="form-control" id="name" placeholder="{{translate('Name')}}" required>
                        </div>
                    </div>
                    <div id="category_select_container">
                        @if ($category->parent_tree=='')
                            <div class="form-group row" data-select-id="0">
                                <label class="col-lg-3 col-from-label">{{translate('Category')}}</label>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" name="parent_ids[]" data-selected="{{ $category->id }}" onchange="get_subcategories(this.value, 0);"data-live-search="true" required>
                                        @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->getTranslation('name') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @php
                            $category_subs=getSubCategories($category->id);
                            @endphp
                            @if (!empty($category_subs) && count($category_subs)>0)
                                <div class="form-group row"  data-select-id="{{$category->id}}">
                                    <label class="col-lg-3 col-from-label"></label>
                                    <div class="col-lg-8">
                                        <select class="form-control aiz-selectpicker" name="parent_ids[]" onchange="get_subcategories(this.value, {{$category->id}});"data-live-search="true">
                                            <option value="">{{translate("Select Category")}}</option>
                                            @foreach ($category_subs as $subcat)
                                            <option value="{{ $subcat->id }}">{{ $subcat->getTranslation('name') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        @else
                            @php
                                $cat_arr=explode(',',$category->parent_tree);
                                $i=1;
                            @endphp
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Parent Category')}}</label>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" name="parent_ids[]" onchange="get_subcategories(this.value, 0);"data-live-search="true" required>
                                        <option value="">{{translate("Select Category")}}</option>
                                        @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                        @if ($cat->id==$cat_arr[0])
                                            selected
                                        @endif
                                        >{{ $cat->getTranslation('name') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @for ($i=0; $i<count($cat_arr);$i++)
                                <div class="form-group row"  data-select-id="{{ $i+1 }}">
                                    <label class="col-lg-3 col-from-label">{{translate('Parent Category')}}</label>
                                    <div class="col-lg-8">
                                        <select class="form-control aiz-selectpicker" name="parent_ids[]" onchange="get_subcategories(this.value, {{ $i+1 }});"data-live-search="true">
                                            <option value="">{{translate("Select Category")}}</option>
                                            @foreach (\App\Models\Category::where('parent_id',$cat_arr[$i])->where('id','!=', $category->id)->get() as $subcat)
                                            <option value="{{ $subcat->id }}" <?php if(in_array($subcat->id,$cat_arr)) {echo "selected";} ?> >{{ $subcat->getTranslation('name') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endfor

                        @endif
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{translate('Ordering Number')}}
                        </label>
                        <div class="col-md-9">
                            <input type="number" name="order_level" value="{{ $category->order_level }}" class="form-control" id="order_level" placeholder="{{translate('Order Level')}}">
                            <small>{{translate('Higher number has high priority')}}</small>
                        </div>
                    </div>
    	            <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Banner')}} <small>({{ translate('200x200') }})</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="banner" class="selected-files" value="{{ $category->banner }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Icon')}} <small>({{ translate('32x32') }})</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="icon" class="selected-files" value="{{ $category->icon }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Meta Title')}}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="meta_title" value="{{ $category->meta_title }}" placeholder="{{translate('Meta Title')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Meta Description')}}</label>
                        <div class="col-md-9">
                            <textarea name="meta_description" rows="5" class="form-control">{{ $category->meta_description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Slug')}}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Slug')}}" id="slug" name="slug" value="{{ $category->slug }}" class="form-control">
                        </div>
                    </div>
                    @if (get_setting('category_wise_commission') == 1)
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Commission Rate')}}</label>
                            <div class="col-md-9 input-group">
                                <input type="number" lang="en" min="0" step="0.01" id="commision_rate" name="commision_rate" value="{{ $category->commision_rate }}" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Filtering Attributes')}}</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker" name="filtering_attributes[]" data-toggle="select2" data-placeholder="Choose ..."data-live-search="true" data-selected="{{ $category->attributes->pluck('id') }}" multiple>
                                @foreach (\App\Models\Attribute::all() as $attribute)
                                    <option value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection
@section('script')
<script type="text/javascript">

    function get_subcategories(category_id, data_select_id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:'{{ route('categories.getSubcategories') }}',
            data:{parent_id: category_id},
            success: function(res) {
                var subcategories=JSON.parse(res);
                var date = new Date();
                //reset subcategories
                $('#category_select_container div').each(function () {
                    if (parseInt($(this).attr('data-select-id')) > parseInt(data_select_id)) {
                        $(this).remove();
                    }
                });
                if (category_id == 0) {
                    return false;
                }
                if (subcategories.length > 0) {
                    var new_data_select_id = date.getTime();
                    var select_tag = '<div class="form-group row" data-select-id="' + category_id + '"><label class="col-md-3 col-from-label">{{translate("Parent Category")}}</label><div class="col-md-8"><select class="form-control aiz-selectpicker subcategories" name="parent_ids[]" onchange="get_subcategories(this.value,' + category_id + ');"';
                    if(category_id!={{$category->parent_id}}){
                        select_tag +=' data-selected="'+data_select_id+'"';
                    }
                    select_tag +='>' +
                        '<option value=""><?php echo translate("Select Category"); ?></option>';
                    for (i = 0; i < subcategories.length; i++) {
                        select_tag += '<option value="' + subcategories[i].id + '">' + subcategories[i].name + '</option>';
                    }
                    select_tag += '</select></div></div>';
                    $('#category_select_container').append(select_tag);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
       });
    }


</script>

@endsection
