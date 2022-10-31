@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="mb-0 h6">{{ translate('Edit Product') }}</h5>
</div>
<div class="">
    <form class="form form-horizontal mar-top" action="{{route('admin.products.update', $product->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-8">
                <input name="_method" type="hidden" value="POST">
                <input type="hidden" name="id" value="{{ $product->id }}">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{translate('Product Name')}} </label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="name" placeholder="{{translate('Product Name')}}" value="{{ $product->name }}" required>
                            </div>
                        </div>
                        <div id="category_select_container">
                            @if ($category->parent_tree=='')
                                <div class="form-group row" data-select-id="0">
                                    <label class="col-lg-3 col-from-label">{{translate('Category')}}</label>
                                    <div class="col-lg-8">
                                        <select class="form-control aiz-selectpicker" name="category_ids[]" data-selected="{{ $category->id }}" onchange="get_subcategories(this.value, 0);"data-live-search="true" required>
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
                                            <select class="form-control aiz-selectpicker" name="category_ids[]" onchange="get_subcategories(this.value, {{$category->id}});"data-live-search="true">
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
                                    <label class="col-lg-3 col-from-label">{{translate('Category')}}</label>
                                    <div class="col-lg-8">
                                        <select class="form-control aiz-selectpicker" name="category_ids[]" onchange="get_subcategories(this.value, 0);"data-live-search="true" required>
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
                                @foreach ($cat_arr as $cat)
                                    <div class="form-group row"  data-select-id="{{ $i }}">
                                        <label class="col-lg-3 col-from-label"></label>
                                        <div class="col-lg-8">
                                            <select class="form-control aiz-selectpicker" name="category_ids[]" onchange="get_subcategories(this.value, {{ $i }});"data-live-search="true">
                                                <option value="">{{translate("Select Category")}}</option>
                                                @foreach (getSubCategories($cat) as $subcat)
                                                <option value="{{ $subcat->id }}" <?php if(in_array($subcat->id,$cat_arr) || $subcat->id==$category->id) {echo "selected";} ?> >{{ $subcat->getTranslation('name') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <?php $i++; ?>
                                @endforeach
                                @php
                                    $category_subs=getSubCategories($category->id);
                                @endphp
                                @if (!empty($category_subs) && count($category_subs)>0)
                                    <div class="form-group row"  data-select-id="{{ $i+1 }}">
                                        <label class="col-lg-3 col-from-label"></label>
                                        <div class="col-lg-8">
                                            <select class="form-control aiz-selectpicker" name="category_ids[]" onchange="get_subcategories(this.value, {{ $i+1 }});"data-live-search="true">
                                                <option value="">{{translate("Select Category")}}</option>
                                                @foreach ($category_subs as $subcat)
                                                <option value="{{ $subcat->id }}">{{ $subcat->getTranslation('name') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="form-group row" id="brand">
                            <label class="col-lg-3 col-from-label">{{translate('Brand')}}</label>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id" data-live-search="true">
                                    <option value="">{{ translate('Select Brand') }}</option>
                                    @foreach (\App\Models\Brand::all() as $brand)
                                    <option value="{{ $brand->id }}" @if($product->brand_id == $brand->id) selected @endif>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if (addon_is_activated('refund_request'))
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{translate('Refundable')}}</label>
                            <div class="col-lg-8">
                                <label class="aiz-switch aiz-switch-success mb-0" style="margin-top:5px;">
                                    <input type="checkbox" name="refundable" @if ($product->refundable == 1) checked @endif value="1">
                                    <span class="slider round"></span></label>
                                </label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Images')}}</h5>
                    </div>
                    <div class="card-body">

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Gallery Images')}}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="photos" value="{{ $product->photos }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Variation')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row gutters-5">
                            <div class="col-lg-3">
                                <input type="text" class="form-control" value="{{translate('Colors')}}" disabled>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker" data-live-search="true" data-selected-text-format="count" name="colors" id="colors">
                                    @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                    <option
                                        value="{{ $color->code }}"
                                        data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>"
                                        <?php if ($color->code == $product->colors) echo 'selected' ?>
                                        ></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="customer_choice_options" id="customer_choice_options">
                            @foreach (json_decode($product->choice_options) as $key => $choice_option)
                            <div class="form-group row">
                                <div class="col-lg-3">
                                    <input type="hidden" name="choice_no[]" value="{{ $choice_option->attribute_id }}">
                                    <input type="text" class="form-control" name="choice[]" value="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->getTranslation('name') }}" placeholder="{{ translate('Choice Title') }}" disabled>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_{{ $choice_option->attribute_id }}[]">
                                        @foreach (\App\Models\AttributeValue::where('attribute_id', $choice_option->attribute_id)->get() as $row)
                                        <option value="{{ $row->value }}" @if( in_array($row->value, $choice_option->values)) selected @endif>
                                            {{ $row->value }}
                                        </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product price')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{translate('Unit price')}}</label>
                            <div class="col-lg-6">
                                <input type="text" placeholder="{{translate('Unit price')}}" name="unit_price" class="form-control" value="{{$product->unit_price}}" required>
                            </div>
                        </div>

                        @php
                          $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                          $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                        @endphp

                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="start_date">{{translate('Discount Date Range')}}</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control aiz-date-range" @if($product->discount_start_date && $product->discount_end_date) value="{{ $start_date.' to '.$end_date }}" @endif name="date_range" placeholder="{{translate('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{translate('Discount')}}</label>
                            <div class="col-lg-6">
                                <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Discount')}}" name="discount" class="form-control" value="{{ $product->discount }}" required>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control aiz-selectpicker" name="discount_type" required>
                                    <option value="amount" <?php if ($product->discount_type == 'amount') echo "selected"; ?> >{{translate('Flat')}}</option>
                                    <option value="percent" <?php if ($product->discount_type == 'percent') echo "selected"; ?> >{{translate('Percent')}}</option>
                                </select>
                            </div>
                        </div>

                        @if(addon_is_activated('club_point'))
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    {{translate('Set Point')}}
                                </label>
                                <div class="col-md-6">
                                    <input type="number" lang="en" min="0" value="{{ $product->earn_point }}" step="1" placeholder="{{ translate('1') }}" name="earn_point" class="form-control">
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Description')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{translate('Description')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                            <div class="col-lg-9">
                                <textarea class="form-control" name="description">{{ $product->description }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Featured')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{translate('Status')}}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="featured" value="1" @if($product->featured == 1) checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Todays Deal')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{translate('Status')}}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="todays_deal" value="1" @if($product->todays_deal == 1) checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Flash Deal')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">
                                {{translate('Add To Flash')}}
                            </label>
                            <select class="form-control aiz-selectpicker" name="flash_deal_id" id="video_provider">
                                <option value="">Choose Flash Title</option>
                                @foreach(\App\Models\FlashDeal::where("status", 1)->get() as $flash_deal)
                                    <option value="{{ $flash_deal->id }}" @if($product->flash_deal_product && $product->flash_deal_product->flash_deal_id == $flash_deal->id) selected @endif>
                                        {{ $flash_deal->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="name">
                                {{translate('Discount')}}
                            </label>
                            <input type="number" name="flash_discount" value="{{ $product->discount }}" min="0" step="1" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="name">
                                {{translate('Discount Type')}}
                            </label>
                            <select class="form-control aiz-selectpicker" name="flash_discount_type" id="">
                                <option value="">Choose Discount Type</option>
                                <option value="amount" @if($product->discount_type == 'amount') selected @endif>
                                    {{translate('Flat')}}
                                </option>
                                <option value="percent" @if($product->discount_type == 'percent') selected @endif>
                                    {{translate('Percent')}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>



            </div>
            <div class="col-12">
                <div class="mb-3 text-right">
                    <button type="submit" name="button" class="btn btn-info">{{ translate('Update Product') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')

<script type="text/javascript">

function add_more_customer_choice_option(i, name){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:'{{ route('seller.products.add-more-choice-option') }}',
            data:{
               attribute_id: i,
            },
            success: function(data) {
                var obj = JSON.parse(data);
                $('#customer_choice_options').append('\
                <div class="form-group row">\
                    <div class="col-md-3">\
                        <input type="hidden" name="choice_options_'+ i +'[]" value="">\
                        <input type="hidden" name="choice_no[]" value="'+i+'">\
                        <input type="text" class="form-control" name="choice[]" value="'+name+'" placeholder="{{ translate('Choice Title') }}" readonly>\
                    </div>\
                    <div class="col-md-8">\
                        <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_'+ i +'[]" multiple>\
                            '+obj+'\
                        </select>\
                    </div>\
                </div>');
                AIZ.plugins.bootstrapSelect('refresh');
           }
       });

    }



    AIZ.plugins.tagify();

    $('#choice_attributes').on('change', function() {
        $.each($("#choice_attributes option:selected"), function(j, attribute){
            flag = false;
            $('input[name="choice_no[]"]').each(function(i, choice_no) {
                if($(attribute).val() == $(choice_no).val()){
                    flag = true;
                }
            });
            if(!flag){
                add_more_customer_choice_option($(attribute).val(), $(attribute).text());
            }
        });

        var str = @php echo $product->attributes @endphp;

        $.each(str, function(index, value){
            flag = false;
            $.each($("#choice_attributes option:selected"), function(j, attribute){
                if(value == $(attribute).val()){
                    flag = true;
                }
            });
            if(!flag){
                $('input[name="choice_no[]"][value="'+value+'"]').parent().parent().remove();
            }
        });

    });

    function get_custom_fields(category_id){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:'{{ route('categories.getCategoryfields') }}',
            data:{id: category_id},
            dataType: 'JSON',
            success: function(res) {
                var len = res.length;
                for(var i=0; i<len; i++){
                    var id = res[i].id;
                    var name = res[i].name;
                    add_more_customer_choice_option(id, name);
                }
            }
       });

    }

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
                    var select_tag = '<div class="form-group row" data-select-id="' + new_data_select_id + '"><label class="col-md-3 col-from-label"></label><div class="col-md-8"><select class="form-control aiz-selectpicker subcategories" name="category_ids[]" onchange="get_subcategories(this.value,' + new_data_select_id + ');">' +
                        '<option value=""><?php echo translate("Select Category"); ?></option>';
                    for (i = 0; i < subcategories.length; i++) {
                        select_tag += '<option value="' + subcategories[i].id + '">' + subcategories[i].name + '</option>';
                    }
                    select_tag += '</select></div></div>';
                    $('#category_select_container').append(select_tag);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
                $('#customer_choice_options').html('');
                get_custom_fields(category_id);
            }
       });
    }

    $(document).ready(function(){

        $('.remove-files').on('click', function(){
            $(this).parents(".col-md-4").remove();
        });
    });

</script>

@endsection
