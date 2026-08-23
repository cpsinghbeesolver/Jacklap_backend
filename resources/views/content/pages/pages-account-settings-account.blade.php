@extends('layouts.contentNavbarLayout')

@section('title', 'Account settings - Account')

@section('page-script')
  @vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
  <div class="row">
    
    
    <div class="col-12">

    <form id="formAccountSettings" method="POST" enctype="multipart/form-data" action="{{ route('update-user', ['id' => $user->id]) }}">
      @csrf

      <div class="card mb-6">
        <!-- Account Header -->
        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center justify-content-between gap-6">

            <!-- Avatar + Upload -->
            <div class="d-flex align-items-start align-items-sm-center gap-6">
              <img src="{{ $image = $user->image ? asset($user->image) : asset('assets/img/avatars/' . rand(1, 7) . '.png'); }}"
                   alt="user-avatar"
                   class="d-block w-px-100 h-px-100 rounded"
                   id="uploadedAvatar" />

              <div class="button-wrapper">
                <label for="upload" class="btn btn-sm btn-primary me-3 mb-4" tabindex="0">
                  <span class="d-none d-sm-block">Upload new photo</span>
                  <i class="ri-upload-2-line d-block d-sm-none"></i>
                  <input type="file"
                         id="upload"
                         name="avatar"
                         class="account-file-input"
                         hidden
                         accept="image/png, image/jpeg" />
                </label>

                <div class="text-muted">
                  Allowed JPG, GIF or PNG. Max size of 800K
                </div>

                @error('avatar')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

              </div>
            </div>

            <!-- Go Back Button -->
            <div>
              <a href="{{ url()->previous() }}" class="btn btn-sm btn-primary">
                <i class="ri-arrow-left-line me-1"></i>
                Go Back
              </a>
            </div>

          </div>
        </div>

        <!-- Account Form Fields -->
        <div class="card-body pt-0">
          <div class="row mt-1 g-5">

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input class="form-control"
                       type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') ?? $user->name }}"
                       autofocus />
                <label for="name">Full Name</label>
                @error('name')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input class="form-control"
                       type="text"
                       id="email"
                       name="email"
                       value="{{ $user->email }}"
                       readonly
                       style="color: rgb(185, 180, 180) !important"
                       placeholder="john.doe@example.com" />
                <label for="email">E-mail</label>
              </div>
                @error('email')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text"
                       id="phone_number"
                       name="phone_number"
                       class="form-control"
                       
                       value="{{ old('phone_number') ?? $user->phone }}" />
                <label for="phone_number">Phone Number</label>
                @error('phone_number')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input class="form-control"
                       type="text"
                       id="organization"
                       name="organization"
                       value="{{ old('organization') ?? $user->organization }}"
                       autofocus />
                <label for="name">Organization</label>
                @error('organization')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>


            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input class="form-control"
                       type="text"
                       id="address"
                       name="address"
                       value="{{ old('address') ?? $user->address }}"
                       autofocus />
                <label for="name">Address</label>
                @error('address')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mt-6">
              <button type="submit" class="btn btn-primary me-3">
                Save changes
              </button>
              <button type="reset" class="btn btn-outline-secondary" onClick="window.location.reload();">
                Reset
              </button>
            </div>

          </div>
        </div>
        <!-- /Account -->
      </div>

    </form>

  </div>



    <div class="card">
      <h5 class="card-header">Delete Account</h5>
      <div class="card-body">
      <form id="formAccountDeactivation" method="POST" action={{ route('delete-user', ['id' => $user->id]) }}>
        @csrf
        @method('delete')
        <div class="form-check mb-6 ms-3">
        <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
        <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
        </div>
        <button type="submit" class="btn btn-danger deactivate-account" disabled>Deactivate
        Account</button>
      </form>
      </div>
    </div>
  </div>
</div>
  
  <script>
    const checkbox = document.getElementById("accountActivation");
    const deactivateBtn = document.querySelector(".deactivate-account");

    checkbox.addEventListener("change", function () {
      deactivateBtn.disabled = !this.checked;
    });
    
  </script>

  <script src="{{ asset('js/sweetalert.js') }}"></script>

  @if (session('success'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: "{{ session('success') }}",
      confirmButtonText: 'OK'
    });
  </script>
  @endif



@endsection