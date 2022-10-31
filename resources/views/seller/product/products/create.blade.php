@extends('frontend.layouts.user_panel')

@section('panel_content')

<div class="aiz-titlebar mt-2 mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Add Your Product') }}</h1>
        </div>
    </div>
</div>

<form class="" action="{{route('seller.products.store')}}" method="POST" enctype="multipart/form-data" id="choice_form">
    <div class="row gutters-5">
        <div class="col-lg-8 m-auto">
            @csrf
            <input type="hidden" name="added_by" value="seller">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Product Name')}}</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name"
                                placeholder="{{ translate('Product Name') }}" required>
                        </div>
                    </div>
                    <div class="form-group row" id="category">
                        <label class="col-md-3 col-from-label">{{translate('Category')}}</label>
                        <div class="col-md-8">
                            <select class="form-control aiz-selectpicker" name="category_ids[]" data-live-search="true" onchange="get_subcategories(this.value, 0);" required>
                                <option value="" disabled selected hidden>{{ translate("Select Category") }}</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="category_select_container"></div>

                    <div class="form-group row" id="brand">
                        <label class="col-md-3 col-from-label">{{translate('Brand')}}</label>
                        <div class="col-md-8">
                            <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id"
                                data-live-search="true">
                                <option value="">{{ translate('Select Brand') }}</option>
                                @foreach (\App\Models\Brand::all() as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if (addon_is_activated('refund_request'))
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Refundable')}}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="refundable" checked value="1">
                                <span></span>
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
                                <input type="hidden" name="photos" class="selected-files">
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
                        <div class="col-md-3">
                            <input type="text" class="form-control" value="{{translate('Colors')}}" disabled>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control aiz-selectpicker" data-live-search="true" name="colors"
                                data-selected-text-format="count" id="colors" data-placeholder="{{ translate('Choose Color') }}" required>
                                <option disabled selected>{{translate("Choose Color")}}</option>
                                @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                <option value="{{ $color->code }}"
                                    data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>">
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" value="{{translate('Attributes')}}" disabled>
                        </div>
                        <div class="col-md-8">
                            <select name="choice_attributes[]" id="choice_attributes"
                                class="form-control aiz-selectpicker" data-live-search="true"
                                data-selected-text-format="count" multiple
                                data-placeholder="{{ translate('Choose Attributes') }}">
                                @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                <option value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}
                        </p>
                        <br>
                    </div>

                    <div class="customer_choice_options" id="customer_choice_options">

                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Product price')}}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Price')}}</label>
                        <div class="col-md-6">
                            <input type="number" lang="en" min="0" value="0" step="0.01"
                                placeholder="{{ translate('Unit price') }}" name="unit_price" class="form-control"
                                required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 control-label" for="start_date">{{translate('Discount Date Range')}}</label>
                        <div class="col-md-9">
                          <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="{{translate('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Discount')}}</label>
                        <div class="col-md-6">
                            <input type="number" lang="en" min="0" value="0" step="0.01"
                                placeholder="{{ translate('Discount') }}" name="discount" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control aiz-selectpicker" name="discount_type">
                                <option value="amount">{{translate('Flat')}}</option>
                                <option value="percent">{{translate('Percent')}}</option>
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
                        <label class="col-md-3 col-from-label">{{translate('Description')}}</label>
                        <div class="col-md-8">
                            <textarea class="form-control" name="description"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="mar-all text-right mb-2">
                <button type="submit" name="button" value="publish"
                    class="btn btn-primary">{{ translate('Upload Product') }}</button>
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
            $(em).closest('.form-group row').remove();
        }

        function delete_variant(em){
            $(em).closest('.variant').remove();
        }

        $('#choice_attributes').on('change', function() {
            $('#customer_choice_options').html(null);
            $.each($("#choice_attributes option:selected"), function(){
                add_more_customer_choice_option($(this).val(), $(this).text());
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
                        console.log(res);
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
                        var select_tag = '<div class="form-group row" data-select-id="' + new_data_select_id + '"><label class="col-md-3 col-from-label">{{translate("subcategory")}}</label><div class="col-md-8"><select class="form-control aiz-selectpicker subcategories" name="category_ids[]" onchange="get_subcategories(this.value,' + new_data_select_id + ');">' +
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

</script>
@endsection
