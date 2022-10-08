@extends('seller.layouts.app')

@section('panel_content')

<div class="aiz-titlebar mt-2 mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Update your product') }}</h1>
        </div>
    </div>
</div>

<form class="" action="{{route('seller.products.update', $product->id)}}" method="POST" enctype="multipart/form-data"
    id="choice_form">
    <div class="row gutters-5">
        <div class="col-lg-8 m-auto">
            <input name="_method" type="hidden" value="POST">
            <input type="hidden" name="lang" value="tr">
            <input type="hidden" name="id" value="{{ $product->id }}">
            <input type="hidden" name="slug" value="{{ $product->slug }}">
            <input type="hidden" name="thumbnail_img" value="{{ $product->thumbnail_img }}">
            @csrf
            <input type="hidden" name="added_by" value="seller">
            <div class="card">

                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-lg-3 col-from-label">{{translate('Product Name')}}</label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="name"
                                placeholder="{{translate('Product Name')}}" value="{{$product->getTranslation('name',$lang)}}"
                                required>
                        </div>
                    </div>
                    <div class="form-group row" id="category">
                        <label class="col-lg-3 col-from-label">{{translate('Category')}}</label>
                        <div class="col-lg-8">
                            <select class="form-control aiz-selectpicker" name="category_id" id="category_id"
                                data-selected="{{ $product->category_id }}" data-live-search="true" required>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                @foreach ($category->childrenCategories as $childCategory)
                                @include('categories.child_category', ['child_category' => $childCategory])
                                @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" id="brand">
                        <label class="col-lg-3 col-from-label">{{translate('Brand')}}</label>
                        <div class="col-lg-8">
                            <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id">
                                <option value="">{{ translate('Select Brand') }}</option>
                                @foreach (\App\Models\Brand::all() as $brand)
                                <option value="{{ $brand->id }}" @if($product->brand_id == $brand->id) selected
                                    @endif>{{ $brand->getTranslation('name') }}</option>
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
                        <label class="col-md-3 col-form-label"
                            for="signinSrEmail">{{translate('Gallery Images')}}</label>
                        <div class="col-md-8">
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="photos" value="{{ $product->photos }}"
                                    class="selected-files">
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
                    <div class="form-group row">
                        <div class="col-lg-3">
                            <input type="text" class="form-control" value="{{translate('Colors')}}" disabled>
                        </div>
                        <div class="col-lg-8">
                            <select class="form-control aiz-selectpicker" data-live-search="true"
                                data-selected-text-format="count" name="colors" id="colors">
                                @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                <option value="{{ $color->code }}"
                                    data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>"
                                    <?php if($color->code== $product->colors) echo 'selected'?>></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-3">
                            <input type="text" class="form-control" value="{{translate('Attributes')}}" disabled>
                        </div>
                        <div class="col-lg-8">
                            <select name="choice_attributes[]" data-live-search="true" data-selected-text-format="count"
                                id="choice_attributes" class="form-control aiz-selectpicker" multiple
                                data-placeholder="{{ translate('Choose Attributes') }}">
                                @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                <option value="{{ $attribute->id }}" @if($product->attributes != null &&
                                    in_array($attribute->id, json_decode($product->attributes, true))) selected
                                    @endif>{{ $attribute->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="">
                        <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}</p>
                        <br>
                    </div>

                    <div class="customer_choice_options" id="customer_choice_options">
                        @foreach (json_decode($product->choice_options) as $key => $choice_option)
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <input type="hidden" name="choice_no[]" value="{{ $choice_option->attribute_id }}">
                                <input type="text" class="form-control" name="choice[]"
                                    value="{{ \App\Models\Attribute::find($choice_option->attribute_id)->getTranslation('name') }}"
                                    placeholder="{{ translate('Choice Title') }}" disabled>
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
                        <label class="col-lg-3 col-from-label">{{translate('Price')}}</label>
                        <div class="col-lg-6">
                            <input type="text" placeholder="{{translate('Unit price')}}" name="unit_price" class="form-control"
                                value="{{$product->unit_price}}" required>
                        </div>
                    </div>

                    @php
                        $date_range = '';
                        if($product->discount_start_date){
                            $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                            $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                            $date_range = $start_date.' to '.$end_date;
                        }
                    @endphp

                    <div class="form-group row">
                        <label class="col-lg-3 col-from-label" for="start_date">{{translate('Discount Date Range')}}</label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control aiz-date-range" value="{{ $date_range }}" name="date_range" placeholder="{{translate('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-from-label">{{translate('Discount')}}</label>
                        <div class="col-lg-6">
                            <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Discount')}}"
                                name="discount" class="form-control" value="{{ $product->discount }}" required>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control aiz-selectpicker" name="discount_type" required>
                                <option value="amount" <?php if($product->discount_type == 'amount') echo "selected";?>>
                                    {{translate('Flat')}}</option>
                                <option value="percent" <?php if($product->discount_type == 'percent') echo "selected";?>>
                                    {{translate('Percent')}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Product Description')}}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-lg-3 col-from-label">{{translate('Description')}}</label>
                        <div class="col-lg-9">
                            <textarea class="form-control"
                                name="description">{{$product->getTranslation('description',$lang)}}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12">
        <div class="mar-all text-right mb-2">
            <button type="submit" name="button" value="publish"
                class="btn btn-primary">{{ translate('Update Product') }}</button>
        </div>
    </div>
    </div>
</form>

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
               attribute_id: i
            },
            success: function(data) {
                var obj = JSON.parse(data);
                $('#customer_choice_options').append('\
                <div class="form-group row">\
                    <div class="col-md-3">\
                        <input type="hidden" name="choice_no[]" value="'+i+'">\
                        <input type="text" class="form-control" name="choice[]" value="'+name+'" placeholder="{{ translate('Choice Title') }}" readonly>\
                    </div>\
                    <div class="col-md-8">\
                        <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_'+ i +'[]">\
                            '+obj+'\
                        </select>\
                    </div>\
                </div>');
                AIZ.plugins.bootstrapSelect('refresh');
           }
       });


    }



    function delete_row(em){
        $(em).closest('.form-group').remove();
    }

    function delete_variant(em){
        $(em).closest('.variant').remove();
    }

    AIZ.plugins.tagify();


    $(document).ready(function(){
        $('.remove-files').on('click', function(){
            $(this).parents(".col-md-4").remove();
        });
    });

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


</script>
@endsection
