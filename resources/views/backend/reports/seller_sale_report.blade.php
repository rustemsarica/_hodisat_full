@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{translate('Seller Based Selling Report')}}</h1>
	</div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">

                <table class="table table-bordered aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Seller Name') }}</th>
                            <th data-breakpoints="lg">{{ translate('Shop Name') }}</th>
                            <th data-breakpoints="lg">{{ translate('Number of Product Sale') }}</th>
                            <th>{{ translate('Order Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sellers as $key => $seller)
                            @if($seller != null)
                                <tr>
                                    <td>{{ $seller->user->name }}</td>
                                    @if($seller->shop != null)
                                        <td>{{ $seller->name }}</td>
                                    @else
                                        <td>--</td>
                                    @endif
                                    <td>
                                        @php
                                            $num_of_sale = 0;
                                            foreach ($seller->user->products as $key => $product) {
                                                $num_of_sale += 1;
                                            }
                                        @endphp
                                        {{ $num_of_sale }}
                                    </td>
                                    <td>
                                        {{ single_price(\App\Models\OrderDetail::where('seller_id', $seller->user->id)->sum('price')) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $sellers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
