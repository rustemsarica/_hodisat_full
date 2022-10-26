@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
			<h1 class="h3">{{translate('All Brands',get_setting('admin_lang'))}}</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-7">
		<div class="card">
		    <div class="card-header row gutters-5">
				<div class="col text-center text-md-left">
					<h5 class="mb-md-0 h6">{{ translate('Brands',get_setting('admin_lang')) }}</h5>
				</div>
				<div class="col-md-4">
					<form class="" id="sort_brands" action="" method="GET">
						<div class="input-group input-group-sm">
					  		<input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter',get_setting('admin_lang')) }}">
						</div>
					</form>
				</div>
		    </div>
		    <div class="card-body">
		        <table class="table aiz-table mb-0">
		            <thead>
		                <tr>
		                    <th>#</th>
		                    <th>{{translate('Name',get_setting('admin_lang'))}}</th>
		                    <th>{{translate('Top',get_setting('admin_lang'))}}</th>
		                    <th class="text-right">{{translate('Options',get_setting('admin_lang'))}}</th>
		                </tr>
		            </thead>
		            <tbody>
		                @foreach($brands as $key => $brand)
		                    <tr>
		                        <td>{{ ($key+1) + ($brands->currentPage() - 1)*$brands->perPage() }}</td>
		                        <td>{{ $brand->name }}</td>
								<td>
		                            <label class="aiz-switch aiz-switch-success mb-0">
                                        <input onchange="brand_top(this)" value="{{ $brand->id }}" type="checkbox" <?php if ($brand->top == 1) echo "checked"; ?> >
                                        <span class="slider round"></span>
                                    </label>
		                        </td>
		                        <td class="text-right">
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('admin.brands.edit', ['id'=>$brand->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ translate('Edit',get_setting('admin_lang')) }}">
		                                <i class="las la-edit"></i>
		                            </a>
		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.brands.destroy', $brand->id)}}" title="{{ translate('Delete',get_setting('admin_lang')) }}">
		                                <i class="las la-trash"></i>
		                            </a>
		                        </td>
		                    </tr>
		                @endforeach
		            </tbody>
		        </table>
		        <div class="aiz-pagination">
                	{{ $brands->appends(request()->input())->links() }}
            	</div>
		    </div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0 h6">{{ translate('Add New Brand',get_setting('admin_lang')) }}</h5>
			</div>
			<div class="card-body">
				<form action="{{ route('admin.brands.store') }}" method="POST">
					@csrf
					<div class="form-group mb-3">
						<label for="name">{{translate('Name',get_setting('admin_lang'))}}</label>
						<input type="text" placeholder="{{translate('Name',get_setting('admin_lang'))}}" name="name" class="form-control" required>
					</div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Logo')}} <small>({{ translate('120x80',get_setting('admin_lang')) }})</small></label>
						<div class="input-group" data-toggle="aizuploader" data-type="image">
							<div class="input-group-prepend">
									<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse',get_setting('admin_lang'))}}</div>
							</div>
							<div class="form-control file-amount">{{ translate('Choose File',get_setting('admin_lang')) }}</div>
							<input type="hidden" name="logo" class="selected-files">
						</div>
						<div class="file-preview box sm">
						</div>
					</div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Meta Title',get_setting('admin_lang'))}}</label>
						<input type="text" class="form-control" name="meta_title" placeholder="{{translate('Meta Title',get_setting('admin_lang'))}}">
					</div>
					<div class="form-group mb-3">
						<label for="name">{{translate('Meta Description',get_setting('admin_lang'))}}</label>
						<textarea name="meta_description" rows="5" class="form-control"></textarea>
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
    function sort_brands(el){
        $('#sort_brands').submit();
    }
    function brand_top(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('admin.brands.change.top') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Brand updated successfully') }}',get_setting('admin_lang'));
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}',get_setting('admin_lang'));
                }
            });
        }
</script>
@endsection
