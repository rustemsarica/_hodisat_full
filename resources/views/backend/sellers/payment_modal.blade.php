<form action="{{ route('admin.commissions.pay_to_seller') }}" method="POST">
    @csrf
    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
    <div class="modal-header">
    	<h5 class="modal-title h6">{{translate('Pay to seller')}}</h5>
    	<button type="button" class="close" data-dismiss="modal">
    	</button>
    </div>
    <div class="modal-body">
      <table class="table table-striped table-bordered" >
          <tbody>
              <tr>
                  @if($shop->user->balance >= 0)
                      <td>{{ translate('Due to seller') }}</td>
                      <td>{{ single_price($shop->user->balance) }}</td>
                  @else
                      <td>{{ translate('Due to admin') }}</td>
                      <td>{{ single_price(abs($shop->user->balance)) }}</td>
                  @endif
              </tr>
                  <tr>
                      <td>{{ translate('Name') }}</td>
                      <td>{{ $shop->bank_name }}</td>
                  </tr>
                  <tr>
                      <td>{{ translate('Iban') }}</td>
                      <td>{{ $shop->bank_acc_name }}</td>
                  </tr>
          </tbody>
      </table>

      @if ($shop->user->balance > 0)
          <div class="form-group row">
              <label class="col-md-3 col-from-label" for="amount">{{translate('Amount')}}</label>
              <div class="col-md-9">
                  <input type="number" lang="en" min="0" step="0.01" name="amount" id="amount" value="{{ $shop->user->balance }}" class="form-control" required>
              </div>
          </div>

          <div class="form-group row">
              <label class="col-md-3 col-from-label" for="payment_option">{{translate('Payment Method')}}</label>
              <div class="col-md-9">
                  <select name="payment_option" id="payment_option" class="form-control aiz-selectpicker" required>
                      <option value="">{{translate('Select Payment Method')}}</option>
                        <option value="bank_payment">{{translate('Bank Payment')}}</option>
                  </select>
              </div>
          </div>
          <div class="form-group row" id="txn_div">
              <label class="col-md-3 col-from-label" for="txn_code">{{translate('Txn Code')}}</label>
              <div class="col-md-9">
                  <input type="text" name="txn_code" id="txn_code" class="form-control">
              </div>
          </div>
      @else
          <div class="form-group row">
              <label class="col-md-3 col-from-label" for="amount">{{translate('Amount')}}</label>
              <div class="col-md-9">
                  <input type="number" lang="en" min="0" step="0.01" name="amount" id="amount" value="{{ abs($shop->user->balance) }}" class="form-control" required>
              </div>
          </div>
          <div class="form-group row" id="txn_div">
              <label class="col-md-3 col-from-label" for="txn_code">{{translate('Txn Code')}}</label>
              <div class="col-md-9">
                  <input type="text" name="txn_code" id="txn_code" class="form-control">
              </div>
          </div>
      @endif
    </div>
    <div class="modal-footer">
      @if ($shop->user->balance > 0)
          <button type="submit" class="btn btn-primary">{{translate('Pay')}}</button>
      @else
          <button type="submit" class="btn btn-primary">{{translate('Clear due')}}</button>
      @endif
      <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
    </div>
</form>

<script>
  $(document).ready(function(){
      $('#payment_option').on('change', function() {
        if ( this.value == 'bank_payment')
        {
          $("#txn_div").show();
        }
        else
        {
          $("#txn_div").hide();
        }
      });
      $("#txn_div").hide();
      AIZ.plugins.bootstrapSelect('refresh');
  });
</script>
