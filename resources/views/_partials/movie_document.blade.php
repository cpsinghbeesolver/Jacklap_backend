<div class="bg-primary rounded p-3 mt-4">
    <div class="row documents">
        <div class="col col-3 col-md-3 col-sm-2 col-lg-2">
            <img src="{{ asset('frontend/img/icons/movie-project/document-icon.svg')}}" class="img-fluid"
            alt="{{ $document->type_title }}">
        </div>
        <div class="col col-7 col-md-6 col-sm-8 col-lg-7 px-0">
            <h4 class="text-white mb-1">{{ $document->type_title }}</h4>
            <p>{{ $document->updated_at->diffForHumans()  }}</p>
        </div>
        <div class="col col-2 col-md-3 col-sm-2 col-lg-3 d-flex justify-content-end align-items-center">
            <a href="{{ $document->getFile() }}" target="_blank" download>
                <img src="{{ asset('frontend/img/icons/movie-project/document-down-load.svg')}}"
                class="img-fluid mx-2 cursor-pointer" alt="{{ $document->type_title }}">
            </a>
            <a href="{{ $document->getFile() }}" target="_blank">
                <img src="{{ asset('frontend/img/icons/movie-project/document-eye.svg')}}"
                class="img-fluid cursor-pointer" alt="{{ $document->type_title }}">
            </a>
        </div>
    </div>
</div>