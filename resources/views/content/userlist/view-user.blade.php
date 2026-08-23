@extends('layouts.contentNavbarLayout')

@section('title', 'View User')

@section('page-script')
  @vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')

<div class="mb-6">
  <div class="card-body">
    <div class="d-flex align-items-start align-items-sm-center justify-content-between gap-6">

      <!-- Avatar + Role -->
      <div class="d-flex align-items-center gap-3 card p-3">
        <img 
          src="{{ $user->image ? asset($user->image) : asset('assets/img/avatars/' . rand(1, 7) . '.png') }}"
          class="rounded"
          width="100"
        />

        <div class="fw-bold text-dark">
          {{ strtoupper($user->getRoleNames()->first() ?? 'NA') }}
        </div>
      </div>

      <!-- Actions -->
      <div class="gap-2 d-flex">

        <a href="{{ route('get-user', ['id' => $user->id, 'role' => request()->query('role')]) }}" class="btn btn-sm btn-outline-primary">
          <i class="ri-edit-2-line"></i>
        </a>

        <button class="btn btn-sm btn-outline-danger delete-user" data-id="{{ $user->id }}">
          <i class="ri-delete-bin-6-line"></i>
        </button>

        <form id="delete-form-{{ $user->id }}" action="{{ route('delete-user', $user->id) }}" method="POST" style="display:none;">
          @csrf
          @method('DELETE')
        </form>

        <a href="{{ route('user-list', ['role' => request('role')]) }}" class="btn btn-sm btn-primary">
          <i class="ri-arrow-left-line me-1"></i> Go Back
        </a>
      </div>

    </div>
  </div>
</div>

<div class="card">
  <h5 class="card-header text-center fw-bold fs-4">
        {{ ucfirst(request('role', 'User')) }} Details
    </h5>

  <div class="table-responsive">
    <table class="table">
      <tbody>

        <tr>
          <td>ID</td>
          <td>{{ $user->id }}</td>
        </tr>

        <tr>
          <td>Name</td>
          <td>{{ $user->name ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Email</td>
          <td>{{ $user->email ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Phone</td>
          <td>{{ $user->phone ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Role</td>
          <td>{{ $user->getRoleNames()->first() ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Address</td>
          <td>{{ $user->address ?? 'NA' }}</td>
        </tr>

      </tbody>
    </table>
  </div>
</div>

{{-- PROFESSIONAL DETAILS --}}
<div class="card mt-4">
  <h5 class="card-header fw-bold">Professional Details</h5>

  <div class="table-responsive">
    <table class="table">
      <tbody>

        <tr>
          <td>Experience</td>
          <td>{{ optional($user->professionalDetail)->experience ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Price</td>
          <td>{{ optional($user->professionalDetail)->price ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Languages</td>
          <td>
            @php
              $languages = optional($user->professionalDetail)->languages;
            @endphp

            {{ is_array($languages) ? implode(', ', $languages) : ($languages ?? 'NA') }}
          </td>
        </tr>

        <tr>
          <td>Service Category</td>
          <td>{{ optional(optional($user->professionalDetail)->serviceCategory)->name ?? 'NA' }}</td>
        </tr>

      </tbody>
    </table>
  </div>
</div>

{{-- BANK DETAILS --}}
<div class="card mt-4">
  <h5 class="card-header fw-bold">Bank Details</h5>

  <div class="table-responsive">
    <table class="table">
      <tbody>

        <tr>
          <td>Account Holder</td>
          <td>{{ optional($user->bankDetail)->account_holder_name ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Bank Name</td>
          <td>{{ optional($user->bankDetail)->bank_name ?? 'NA' }}</td>
        </tr>

        <tr>
          <td>Account Number</td>
          <td>
            @if($user->bankDetail && $user->bankDetail->account_number)
              ****{{ substr($user->bankDetail->account_number, -4) }}
            @else
              NA
            @endif
          </td>
        </tr>

        <tr>
          <td>IFSC Code</td>
          <td>{{ optional($user->bankDetail)->ifsc_code ?? 'NA' }}</td>
        </tr>

      </tbody>
    </table>
  </div>
</div>

<div class="card mt-4">
    <h5 class="card-header fw-bold">Media Documents</h5>

    <div class="card-body">

        @if($user->media && $user->media->count())

            <div class="row">

                @foreach($user->media as $media)

                    <div class="col-md-4 mb-4">

                        <div class="border rounded p-3 h-100">

                            <h6 class="fw-bold text-center mb-3">
                                {{ optional($media->identityType)->name ?? 'Document' }}
                            </h6>

                            @if($media->files && $media->files->count())

                                <div class="d-flex flex-wrap gap-2 justify-content-center">

                                    @foreach($media->files as $file)

                                        @php
                                            $extension = strtolower(pathinfo($file->url, PATHINFO_EXTENSION));
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp

                                        @if($isImage)
                                            <a href="{{ asset($file->url) }}" target="_blank">
                                                <img
                                                    src="{{ asset($file->url) }}"
                                                    width="120"
                                                    height="120"
                                                    class="rounded border"
                                                    style="object-fit:cover"
                                                >
                                            </a>
                                        @else
                                            <div class="text-center">
                                                <a
                                                    href="{{ asset($file->url) }}"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    View File
                                                </a>
                                            </div>
                                        @endif

                                    @endforeach

                                </div>

                            @else

                                <p class="text-muted text-center mb-0">
                                    No files uploaded
                                </p>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <p class="text-muted">No media found</p>

        @endif

    </div>
</div>
@endsection
@section('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-user').forEach(button => {
      button.addEventListener('click', function () {

        const userId = this.dataset.id;

        Swal.fire({
          title: 'Are you sure?',
          text: 'This action cannot be undone!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#d33'
        }).then((result) => {
          if (result.isConfirmed) {
            document.getElementById(`delete-form-${userId}`).submit();
          }
        });

      });
    });
  });
  </script>
@endsection