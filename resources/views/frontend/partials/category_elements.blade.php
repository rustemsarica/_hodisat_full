@php
if(count(getHeaderCategories($category->id))>=5){
    $column=5;
}
else{
    $column=count(getHeaderCategories($category->id));
}
@endphp
<div class="card-columns " style="column-count: {{$column}} !important;">
    @foreach (getHeaderCategories($category->id) as $first_level)
        <div class="card shadow-none border-0 text-left d-block" style="width:200px;">
            <ul class="list-unstyled mb-3">
                <li class="fw-600 border-bottom pb-2 mb-3">
                    <a class="text-reset" href="{{ route('products.category', $first_level->slug) }}">{{ $first_level->getTranslation('name') }}</a>
                </li>
                @foreach (getHeaderCategories($first_level->id, 5) as $second_level)
                    <li class="mb-2">
                        <a class="text-reset" href="{{ route('products.category', $second_level->slug) }}">{{ $second_level->getTranslation('name') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
