@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Offers') }}</h5>
        </div>
        @if (count($offers) > 0)
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th data-breakpoints="md">{{ translate('Date')}}</th>
                            <th>{{ translate('Product')}}</th>
                            <th>{{ translate('Amount')}}</th>
                            <th class="text-right">{{ translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($offers as $key => $offer)

                                <tr>
                                    <td>{{ date('d.m.Y H:i', strtotime($offer->created_at)) }}</td>
                                    <td>
                                        {{$offer->product->name}}
                                    </td>
                                    <td>
                                        {{ single_price($offer->offer_value) }}
                                    </td>
                                    <td class="text-right">
                                        @if ($offer->answer == 2)
                                        <div class="row text-right">
                                            <form action="{{ route('offer.answer') }}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$offer->id}}">
                                                <input type="hidden" name="answer" value="1">
                                                <button type="submit" class="btn btn-soft-success btn-icon btn-circle btn-sm"><i class="las la-check"></i></button>
                                            </form>
                                            <form action="{{ route('offer.answer') }}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$offer->id}}">
                                                <input type="hidden" name="answer" value="0">
                                                <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm"><i class="las la-times"></i></button>
                                            </form>
                                        </div>
                                        @elseif($offer->answer == 1)
                                            {{translate("Accepted")}}
                                        @elseif($offer->answer == 0)
                                            {{translate("Denied")}}
                                        @endif
                                    </td>
                                </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $offers->links() }}
              	</div>
            </div>
        @endif
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')

    <div class="modal fade" id="offer_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div id="offer-details-modal-body">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        $('#offer_details').on('hidden.bs.modal', function () {
            location.reload();
        })
    </script>

@endsection
