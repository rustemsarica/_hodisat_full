@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0 h6 text-center">{{translate('Seller Commission Activatation')}}</h3>
                </div>
                <div class="card-body text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'vendor_commission_activation')" <?php if(get_setting('vendor_commission_activation') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0 h6 text-center">{{translate('Category Based Commission')}}</h3>
                </div>
                <div class="card-body text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'category_wise_commission')" <?php if(get_setting('category_wise_commission') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
              <div class="card-header">
                  <h5 class="mb-0 h6">{{translate('Seller Commission')}}</h5>
              </div>
              <div class="card-body">
                  <form class="form-horizontal" action="{{ route('admin.business_settings.vendor_commission.update') }}" method="POST" enctype="multipart/form-data">
                  	@csrf

                    <div class="form-group row">
                        <label class="col-md-4 col-from-label">{{translate('Seller Commission')}}</label>
                        <div class="col-md-8">
                            <input type="hidden" name="types[]" value="vendor_commission_type">
                            <select class="form-control aiz-selectpicker" name="vendor_commission_type" onchange="change_commission_type(this.value)">
                                <option value="">{{translate('Commission Type')}}</option>
                                <option value="amount"  @if(get_setting('vendor_commission_type') == 'amount') selected @endif >{{translate('Amount')}}</option>
                                <option value="percent" @if(get_setting('vendor_commission_type') == 'percent') selected @endif >{{translate('Percent')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-4 col-from-label">{{translate('Seller Commission')}}</label>
                        <div class="col-md-8">
                            <input type="hidden" name="types[]" value="vendor_commission">
                            <div class="input-group">
                                <input type="number" lang="en" min="0" step="0.01" value="{{ get_setting('vendor_commission') }}" placeholder="{{translate('Seller Commission')}}" name="vendor_commission" class="form-control">
                                <div class="input-group-append">
                                    <span id="commission_type" class="input-group-text">
                                        @if(get_setting('vendor_commission_type') == 'percent')
                                            %
                                        @elseif(get_setting('vendor_commission_type') == 'amount')
                                            ₺
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                  </form>
              </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Note')}}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item text-muted">
                            1. {{ get_setting('vendor_commission') }}% {{translate('of seller product price will be deducted from seller earnings') }}.
                        </li>
                        <li class="list-group-item text-muted">
                            2. {{translate('If Category Based Commission is enbaled, Set seller commission percentage 0.') }}.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
              <div class="card-header">
                  <h5 class="mb-0 h6">{{translate('Withdraw Seller Amount')}}</h5>
              </div>
              <div class="card-body">
                  <form class="form-horizontal" action="{{ route('admin.business_settings.vendor_commission.update') }}" method="POST" enctype="multipart/form-data">
                  	@csrf
                    <div class="form-group row">
                        <label class="col-md-4 col-from-label">{{translate('Minimum Seller Amount Withdraw')}}</label>
                        <div class="col-md-8">
                            <input type="hidden" name="types[]" value="minimum_seller_amount_withdraw">
                            <div class="input-group">
                                <input type="number" lang="en" min="0" step="0.01" value="{{ get_setting('minimum_seller_amount_withdraw') }}" placeholder="{{translate('Minimum Seller Amount Withdraw')}}" name="minimum_seller_amount_withdraw" class="form-control" required>
                            </div>
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

        function change_commission_type(val){
            if(val == 'amount'){
                $('#commission_type').html('₺');
            }else if(val == 'percent'){
                $('#commission_type').html('%');
            }
        }

        function updateSettings(el, type){
            if($(el).is(':checked')){
                var value = 1;
            }
            else{
                var value = 0;
            }

            $.post('{{ route('admin.business_settings.update.activation') }}', {_token:'{{ csrf_token() }}', type:type, value:value}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
