@extends('frontend.layouts.app')

@section('content')
    <section class="hero_banner_section">
        <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
            <h1 class="banner_heading">
                On-Demand Lifestyle<br />

                <span class="banner_green_heading"
                >Services for Customers &<br />
                Providers</span
                >
            </h1>

            <p class="home_paragraph">
                Reliable, fast, and trusted home services, from salon and
                <br />cleaning to yoga, tutoring, nanny care, and more.
            </p>

            <div class="hero-buttons">
                <button class="provider-btnn">Book a Service</button>

                <button class="provider-btnn">Become a Service Provider</button>
            </div>
            </div>

            <div class="col-lg-6">
            <div class="banner_img">
                <img src="{{ asset('frontend/images/banner_img.jpg') }}" class="hero-img" />
            </div>
            </div>
        </div>
        </div>
    </section>
    <!-- Bottom Section - Service Cards (Col-lg-12) -->
    <section class="service-card-section">
        <div class="container">
            <div class="service-cards-container p-5 mx-lg-5 mx-0">
                <div class="row g-4 justify-content-lg-between">
                    @include('_partials.service-categories',['theme' => 'light'])
                </div>
            </div>
        </div>
    </section>
    <section class="about-section py-5">
        <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-4">
            <h2 class="about-title">About <span>JackLap</span></h2>

            <p class="about-text">
                JackLap is an on-demand lifestyle and IT-enabled services platform
                designed to deliver convenient, reliable, and high-quality
                services directly to your doorstep.
            </p>

            <p class="about-text">
                Built with a strong technology foundation, JackLap ensures
                efficient operations, real-time tracking, and a smooth service
                journey.
            </p>
            </div>

            <div class="col-lg-8">
            <div class="image-wrapper">
                <img src="{{ asset('frontend/images/about_jacklap2.jpg')}}" class="img-main" alt="" />
                <img
                src="{{ asset('frontend/images/about_jacklap1.jpg')}}"
                class="img-overlay"
                alt=""
                />
            </div>
            </div>
        </div>
        </div>
    </section>
    <section class="benefits-section py-5">
        <div class="container">
        <div class="row align-items-center benefit_service_provider">
            <div class="col-lg-6">
            <h2 class="benefits-heading">
                Benefits for<br /><span class="banner_green_heading">
                Service Providers?</span
                >
            </h2>

            <p class="benefits-subheading">
                Join JackLap and grow your income with <br />flexible, on-demand
                work.
            </p>
            </div>

            <div class="col-lg-6">
            <div class="benefits-content">
                <ul class="benefits-list">
                <li class="benefit-item">
                    <i class="fa-solid fa-check"></i>
                    <span>Earn more with every completed service</span>
                </li>

                <li class="benefit-item">
                    <i class="fa-solid fa-check"></i>
                    <span>Flexible working hours</span>
                </li>

                <li class="benefit-item">
                    <i class="fa-solid fa-check"></i>
                    <span>Build your professional profile</span>
                </li>

                <li class="benefit-item">
                    <i class="fa-solid fa-check"></i>
                    <span>Work According to Your Location</span>
                </li>
                </ul>
            </div>
            </div>
        </div>
        </div>
    </section>

    <!-- ===== SERVICES SHOWCASE WITH CIRCLES ===== -->
    <section class="services-section py-5" id="services">
    <div class="container">
        
        <!-- Main Content Container -->
        <div class="services-container overflow-hidden">
            
            <!-- Background Circles-->
            <div class="circle-1"></div>
            <div class="circle-2"></div>

            <!-- Section Header -->
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="services-title display-4 fw-medium mb-3">
                        Looking for Trusted <br> <em>Home Services?</em>
                    </h2>
                    <p class="services-description">
                        JackLap connects you with trusted professionals for salon, cleaning, tutoring, driver, and home services, all bookable instantly at your convenience.
                    </p>
                    <a href="#" class="btn btn-services mt-3">
                        Explore Jacklap Services
                    </a>
                </div>
            </div>
            
            <!-- Service Cards -->
            <div id="dark-services-section" class="services-cards-wrapper pt-5 mx-lg-5">
                <div class="row g-4 justify-content-center">
                    <!-- Service Card 1 - Salon -->
                    @include('_partials.service-categories',['theme' => 'dark'])
                </div>
            </div>
        </div>
    </div>
    </section>

    <section class="how-it-works-section py-5">
        <div class="how-it-works-container">
        <div class="how-it-works-header">
            <h2 class="how-it-works-title">How it <span>Works</span></h2>

            <p class="how-it-works-text">
            Choose your service, set your time, and let our experts bring
            comfort and care to your doorstep.
            </p>
        </div>

        <div class="how-it-works-box">
            <div class="how-it-works-item">
            <i class="fa-solid fa-house"></i>
            <h4>Choose your Service</h4>
            </div>

            <div class="how-it-works-divider"></div>

            <div class="how-it-works-item">
            <i class="fa-regular fa-clock"></i>
            <h4>Book in Minutes</h4>
            </div>

            <div class="how-it-works-divider"></div>

            <div class="how-it-works-item">
            <i class="fa-regular fa-user"></i>
            <h4>Get Matched with Experts</h4>
            </div>
        </div>
        </div>
    </section>
    <section class="jacklap-app-section py-5">
        <div class="container py-4">
        <div class="jacklap-app-container">
            <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title display-4 fw-medium mb-3">
                Get the <em>JackLap App Now!</em>
                </h2>
                <p class="mb-4">
                Book trusted home services or grow your business by offering
                services, all in one powerful platform.
                </p>
                <div class="d-flex gap-3">
                <a href="#" class="px-4 py-3 provider-btnn"
                    >Scan to Download</a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0">
                <img
                src="{{ asset('frontend/images/JackLap-App.svg')}}"
                class="img-fluid"
                alt="JackLap-App"
                />
            </div>
            </div>
        </div>
        </div>
    </section>
    <section class="home_form_section">
        <div class="container home_form_">
        <div class="home_form_text">
            <h2>
            Let’s Connect and <br />
            <span>Make Things Happen</span>
            </h2>

            <p>
            Get in touch with us for support, service inquiries, or partnership
            opportunities. We’ll respond as quickly as possible.
            </p>
        </div>

        <div class="home_form_box">
            <form>
            <input type="text" placeholder="Your Name" />
            <input type="email" placeholder="Email Address" />
            <label>I am a</label><br />
            <div class="home_form_radio">
                <label class="radio_item">
                <input type="radio" name="type" />
                <span></span>
                Service Provider
                </label>

                <label class="radio_item">
                <input type="radio" name="type" />
                <span></span>
                Service Seeker
                </label>
            </div>

            <textarea placeholder="Message"></textarea>

            <button type="submit" class="home_form_btn">Submit</button>
            </form>
        </div>
        </div>
    </section>
@endsection