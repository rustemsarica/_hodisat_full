@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <form class="" action="" id="sort_supports" method="GET">
                <div class="card-header row gutters-5">
                    <div class="col">
                        <h5 class="mb-md-0 h6">{{ translate('Support') }}</h5>
                    </div>

                    <div class="col-lg-2 ml-auto">
                        <select class="form-control aiz-selectpicker" name="parent_id" id="parent_id">
                            <option value="">{{translate('Parent Category')}}</option>
                            @foreach (\App\Models\Support::where('parent_id',0)->get() as $item)
                                <option value="{{$item->id}}" @if ($parent_id == '{{$item->id}}') selected @endif>{{$item->title}}</option>
                            @endforeach


                        </select>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control" id="search" name="search"@isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Search') }}">
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                        </div>
                    </div>

                </div>

                <div class="card-body">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th>Resim</th>
                                <th>Ana Kategori</th>
                                <th>Başlık</th>
                                <th>İçerik</th>
                                <th class="text-right" width="15%">{{translate('options')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($supports as $key => $support)
                            <tr>
                                <td>
                                    @if ($support->icon!=null)
                                        <img style="max-height:100px; max-width:100px" src="{{uploaded_asset($support->icon)}}" alt="">
                                    @elseif($support->image_url!=null)
                                        <img style="max-height:100px; max-width:100px" src="{{$support->image_url}}" alt="">
                                    @endif
                                </td>
                                <td>
                                    @if($support->parent_id!=0)
                                    {{ \App\Models\Support::where('id',$support->parent_id)->first()->title }}
                                    @endif

                                </td>
                                <td>
                                    {{ $support->title }}
                                </td>
                                <td>
                                    {{ $support->text }}
                                </td>

                                <td class="text-right">

                                    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('support_ticket.getSupport', $support->id) }}" title="{{ translate('View') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    @if ($support->parent_id!=0)
                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('support_ticket.deleteSupport', $support->id)}}" title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    @endif

                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="aiz-pagination">
                        {{ $supports->appends(request()->input())->links() }}
                    </div>

                </div>
            </form>
        </div>
    </div>
    <div class="col-md-5">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0 h6">{{ translate('Add New',get_setting('admin_lang')) }}</h5>
			</div>
			<div class="card-body">
				<form action="{{ route('support_ticket.addSupportPost') }}" method="POST">
					@csrf
                    <select class="form-control aiz-selectpicker" name="parent_id" id="parent_id">
                        <option value="">{{translate('Parent Category')}}</option>
                        @foreach (\App\Models\Support::get() as $item)
                            <option value="{{$item->id}}" @if ($parent_id == '{{$item->id}}') selected @endif>{{$item->title}}</option>
                        @endforeach


                    </select>
					<div class="form-group mb-3">
						<label for="name">{{translate('Title',get_setting('admin_lang'))}}</label>
						<input type="text" placeholder="{{translate('Title',get_setting('admin_lang'))}}" name="title" class="form-control" required>
					</div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Icon')}} <small>({{ translate('120x80',get_setting('admin_lang')) }})</small></label>
						<div class="input-group" data-toggle="aizuploader" data-type="image">
							<div class="input-group-prepend">
									<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse',get_setting('admin_lang'))}}</div>
							</div>
							<div class="form-control file-amount">{{ translate('Choose File',get_setting('admin_lang')) }}</div>
							<input type="hidden" name="icon" class="selected-files">
						</div>
						<div class="file-preview box sm">
						</div>
					</div>
					<div class="form-group mb-3">
						<label for="image_url">{{translate('Image url',get_setting('admin_lang'))}}</label>
						<input type="text" class="form-control" name="image_url" placeholder="{{translate('Image url',get_setting('admin_lang'))}}">
					</div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Text',get_setting('admin_lang'))}}</label>
						<textarea name="text" rows="5" class="form-control"></textarea>
					</div>
					<div class="form-group mb-3 text-right">
						<button type="submit" class="btn btn-primary">{{translate('Save',get_setting('admin_lang'))}}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">


    </script>
@endsection
