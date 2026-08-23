@foreach($serviceCategories as $category)
    @if($theme == 'dark')
        <div class="col">
            <div class="service-cardd">
                <h5 class="service-cardd-title">{{$category->name}}</h5>
                <div class="service-cardd-image">
                    <img src="{{ asset($category->image)}}" alt="{{$category->name}}">
                </div>
            </div>
        </div>
    @else
        <div class="col">
            <div class="service-card">
                <h5 class="service-card-title">{{$category->name}}</h5>
                <div class="service-icon-wrapper">
                    <img src="{{ asset($category->image)}}" alt="{{$category->name}}">
                </div>
            </div>
        </div>
    @endif
@endforeach
            