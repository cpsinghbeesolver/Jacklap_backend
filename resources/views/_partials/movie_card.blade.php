<div class="col">
    <a href="{{ route('user.movie.project',['movie_id' => $movie->id]) }}">
        <div class="movie-card">
            <div class="grade">
                <div class="d-flex justify-content-between">
                    <span>GRADE B</span>
                    <img src="{{ asset('frontend/img/icons/heart.svg') }}" alt="Movie Poster">
                </div>
            </div>
            <img class="img-fluid poster-thumb" src="{{ $movie->getImage() }}" alt="Movie Poster">
            <div class="overlay">
                <h2>{{ $movie->name }}</h2>
                <div class="d-flex justify-content-between mb-2 align-items-center">
                    <p>Budget: ${{ $movie->budget?->budget_amount }}</p> |
                    <p>Available: ${{ $movie->budget?->plan_amount }}</p>
                </div>
                <button class="btn btn-light">Learn More</button>
            </div>
        </div>
    </a>
</div>
