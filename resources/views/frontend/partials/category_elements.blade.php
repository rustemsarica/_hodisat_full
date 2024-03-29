
<div class="card-columns" style="column-count: 5 !important;">
    @foreach (getHeaderCategories($category->id) as $first_level)
        <div class="card shadow-none border-0 text-left" >
            <ul class="list-unstyled mb-3">
                <li class="fw-600 border-bottom pb-2 mb-3">
                    <a class="text-reset fs-16" href="{{ route('products.category', $first_level->slug) }}">{{ $first_level->getTranslation('name') }}</a>
                </li>
                @foreach (getHeaderCategories($first_level->id, 5) as $second_level)
                    <li class="mb-2">
                        <a class="text-reset fs-14" href="{{ route('products.category', $second_level->slug) }}">{{ $second_level->getTranslation('name') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
