<div class="py-3">
    <div class="row team">
        <div class="col-lg-2 col-2 col-md-3">
        <img src="{{ $crew->getImage()}}" class="img-fluid rounded" alt="{{ $crew->name }}">
        </div>
        <div class="col-lg-10 col-10 col-md-9 px-0">
        <h4 class="text-white mb-1">{{ $crew->name }}</h4>
        <p>{{ $crew->position }}</p>
        </div>
    </div>
</div>