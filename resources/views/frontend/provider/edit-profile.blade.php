@extends('frontend.layouts.provider-layout')

@section('content')
    <section class="view_profile_section">
    <div class="container">
      <div class="row">
        <!-- LEFT SIDEBAR -->
        <div class="col-md-5">
          <div class="view_profile_sidebar">
            <h6>Profile</h6>
            <a href="provider_profile_edit.html" class="active">Edit Profile</a>

            <hr />

            <h6>Earnings</h6>
            <a href="provider_myearning.html">My Earnings</a>
          </div>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="col-md-7">
          <!-- BASIC DETAILS -->
          <div class="view_profile_card">
            <div class="view_profile_header">
              <h5>Basic Details</h5>
              <i class="bi bi-pencil"></i>
            </div>

            <div class="view_profile_body">
              <p><span>Full Name:</span> Wade Warren</p>
              <p><span>Gender:</span> Male</p>
              <p><span>DOB:</span> 24/12/2002</p>
              <p>
                <span>Email:</span> 3891 Ranchview Dr. Richardson, California
                62639
              </p>
              <p><span>Phone:</span> 96xxxxxxxx</p>
              <p><span>Password:</span> ********</p>
            </div>
          </div>

          <!-- SERVICE DETAILS -->
          <div class="view_profile_card">
            <div class="view_profile_header">
              <h5>Service Details</h5>
              <i class="bi bi-pencil"></i>
            </div>

            <div class="view_profile_body">
              <p><span>Service Category:</span> Wade Warren</p>
              <p><span>Experience:</span> 2 years</p>

              <p class="view_profile_label">
                Describe what this service includes
              </p>
              <div class="view_profile_desc">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
                do eiusmod tempor incididunt ut labore et dolore magna aliqua.
              </div>

              <p><span>Price:</span> $100</p>
              <p><span>Languages Known:</span> Punjabi, Hindi and English</p>

              <h6 class="view_profile_green">Additional Services</h6>

              <p><span>Service name:</span> Mopping</p>
              <p><span>Service Price:</span> $50</p>
              <div class="view_profile_add_service">
                <span class="green">Add a Service</span>
                <i class="bi bi-plus"></i>
              </div>
            </div>
          </div>
          <!-- VERIFICATION -->
          <div class="view_profile_card">
            <div class="view_profile_header">
              <h5>Verification</h5>
              <i class="bi bi-pencil"></i>
            </div>

            <div class="view_profile_body">
              <p><span>ID Proof:</span> Visa</p>

              <div class="view_profile_file">
                <div>
                  <i class="bi bi-file-earmark-pdf"></i>
                  <div>
                    <p class="file-name">Document.pdf</p>
                    <small>20 KB</small>
                  </div>
                </div>
                <i class="bi bi-x"></i>
              </div>

              <p class="mt-3"><span>Certificates:</span></p>

              <div class="view_profile_file">
                <div>
                  <i class="bi bi-file-earmark-pdf"></i>
                  <div>
                    <p class="file-name">Document.pdf</p>
                    <small>20 KB</small>
                  </div>
                </div>
                <i class="bi bi-x"></i>
              </div>

              <p class="mt-3"><span>Profile photo:</span></p>

              <div class="view_profile_file">
                <div>
                  <i class="bi bi-file-earmark-pdf"></i>
                  <div>
                    <p class="file-name">Document.pdf</p>
                    <small>20 KB</small>
                  </div>
                </div>
                <i class="bi bi-x"></i>
              </div>
            </div>
          </div>

          <!-- BANK DETAILS -->
          <div class="view_profile_card">
            <div class="view_profile_header">
              <h5>Bank Details</h5>
              <i class="bi bi-pencil"></i>
            </div>

            <div class="view_profile_body">
              <p><span>Account holder name:</span> Shruti Singh</p>
              <p><span>Bank Name:</span> HDFC Bank</p>
              <p><span>Account Number:</span> 12344555555</p>
              <p><span>IFSC Code:</span> 12344555555</p>
              <p><span>Account Type:</span> Saving</p>
              <div class="view_profile_save_wrap">
                <a href="provider_profile_view.html" class="view_profile_save_btn"
                  >Save changes</a
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection