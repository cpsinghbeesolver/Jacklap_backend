<footer class="footer_section">
    <div class="footer_container">
      <div class="col-lg-3">
        <div class="footer_col">
          <img src="{{ asset('frontend/images/white_logo.png') }}" class="logo" />
          <p>
            JackLap is an on-demand lifestyle and IT-enabled services platform
            designed to deliver convenient, reliable, and high-quality
            services directly to your doorstep
          </p>
        </div>
      </div>
      <div class="footer_col">
        <h4>Our Services</h4>
        <ul class="footer_links">
          @foreach($serviceCategories as $category)
            <li>{{$category->name}}</li>
          @endforeach
        </ul>
      </div>

      <div class="footer_col">
        <h4>Contact Us</h4>
        <ul class="footer_links">
          <li>Help and Support</li>
        </ul>
      </div>

      <div class="footer_col">
        <div class="footer_subscribe">
          <input type="email" placeholder="Enter your email" />
          <button>Subscribe Now</button>
        </div>
        @include('_partials.social')
      </div>
    </div>

    <div class="footer_bottom">
      <p>Copyright © 2025 JackLap and The Long Term Care</p>

      <div class="footer_policy">
        <span>Term of use</span>
        <span>|</span>
        <span>Privacy Policy</span>
        <span>|</span>
        <span>Cookie Policy</span>
      </div>
    </div>
  </footer>