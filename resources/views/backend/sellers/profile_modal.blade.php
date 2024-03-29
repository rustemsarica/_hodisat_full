<div class="modal-body">

  <div class="text-center">
      <span class="avatar avatar-xxl mb-3">
          <img src="{{ uploaded_asset($shop->logo) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
      </span>
      <h1 class="h5 mb-1">{{ $shop->user->name }}</h1>
      <p class="text-sm text-muted">{{ $shop->user->username }}</p>

      <div class="pad-ver btn-groups">
          <a href="{{ $shop->facebook }}" class="btn btn-icon demo-pli-facebook icon-lg add-tooltip" data-original-title="Facebook" data-container="body"></a>
          <a href="{{ $shop->twitter }}" class="btn btn-icon demo-pli-twitter icon-lg add-tooltip" data-original-title="Twitter" data-container="body"></a>
          <a href="{{ $shop->google }}" class="btn btn-icon demo-pli-google-plus icon-lg add-tooltip" data-original-title="Google+" data-container="body"></a>
      </div>
  </div>
  <hr>

  <!-- Profile Details -->
  <h6 class="mb-4">{{translate('About')}} {{ $shop->user->name }}</h6>
  <p><a href="{{ route('shop.visit', $shop->user->username) }}" class="btn-link"><i class="demo-pli-internet icon-lg icon-fw mr-1"></i>{{ $shop->user->username }}</a></p>
  <p><i class="demo-pli-old-telephone icon-lg icon-fw mr-1"></i>{{ $shop->user->phone }}</p>

  <h6 class="mb-4">{{translate('Payout Info')}}</h6>
  <p>{{translate('Name')}} : {{ $shop->bank_name }}</p>
  <p>{{translate('Iban')}} : {{ $shop->bank_acc_name }}</p>

  <br>

  <div class="table-responsive">
      <table class="table table-striped mar-no">
          <tbody>
          <tr>
              <td>{{ translate('Total Products') }}</td>
              <td>{{ App\Models\Product::where('user_id', $shop->user_id)->get()->count() }}</td>
          </tr>
          <tr>
              <td>{{ translate('Total Orders') }}</td>
              <td>{{ App\Models\OrderDetail::where('seller_id', $shop->user_id)->get()->count() }}</td>
          </tr>
          <tr>
              <td>{{ translate('Total Sold Amount') }}</td>
              @php
                  $orderDetails = \App\Models\OrderDetail::where('seller_id', $shop->user_id)->get();
                  $total = 0;
                  foreach ($orderDetails as $key => $orderDetail) {
                      if($orderDetail->order != null && $orderDetail->order->payment_status == 'paid'){
                          $total += $orderDetail->price;
                      }
                  }
              @endphp
              <td>{{ single_price($total) }}</td>
          </tr>
          <tr>
              <td>{{ translate('Wallet Balance') }}</td>
              <td>{{ single_price($shop->user->balance) }}</td>
          </tr>
          </tbody>
      </table>
  </div>
</div>
