<div class="row row-cols-1 row-cols-md-2 row-cols-sm-2 row-cols-lg-4 g-4 mt-2 home-row" id="movie-list">
    @foreach($movies as $movie)
        @include('_partials.movie_card', ['movie' => $movie])
    @endforeach
</div>

@if ($movies->hasMorePages())
    <div class="text-center mt-4 load-more">
        <button id="load-more" data-url="{{ $movies->nextPageUrl() }}" class="btn btn-outline-primary bg-transparent rounded-pill px-5 border">
            Load More
        </button>
    </div>
@endif
