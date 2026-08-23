@php
use App\Models\SocialLink;

$socialLinks = SocialLink::where('status', 1)
                ->orderBy('sort_order')
                ->get();
@endphp
<div class="footer_social">
    @foreach($socialLinks as $link)
        <a href="{{ $link->url }}" target="_blank" class="text-decoration-none">
            <i class="{{ $link->icon }} text-decoration-none"></i>
        </a>
    @endforeach
</div>