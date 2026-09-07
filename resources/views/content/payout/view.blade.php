@extends('layouts.contentNavbarLayout')
@section('title', 'Payout Request Details')

@section('content')
<div class="card">
    <div class="justify-content-between d-flex">
        <h5 class="card-header">Payout Request Details</h5>
        <div class="m-4">
            <a href="{{ route('payout-list') }}" class="btn btn-sm btn-primary">
                <i class="ri-arrow-left-line me-1"></i>
                Go Back
            </a>
        </div>
    </div>

    <div class="card-body">

        <p><b>Provider:</b> {{ $payoutRequest->provider->name ?? 'N/A' }} ({{ $payoutRequest->provider->email ?? '' }})</p>

        <p><b>Requested Amount:</b> {{ number_format($payoutRequest->amount, 2) }} {{ strtoupper($payoutRequest->currency) }}</p>

        <p><b>Status:</b>
            @php
                $colors = ['pending'=>'warning','transferred'=>'info','processing'=>'info','paid'=>'success','rejected'=>'danger','failed'=>'danger'];
                $color = $colors[$payoutRequest->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $color }}">{{ ucfirst($payoutRequest->status) }}</span>
        </p>

        <p><b>Provider's Current Remaining Balance:</b> {{ number_format($ledger['remaining'], 2) }} {{ strtoupper($ledger['currency']) }}</p>
        <p><b>Provider's Total Earned:</b> {{ number_format($ledger['total_earned'], 2) }}</p>
        <p><b>Currently Locked (incl. this request):</b> {{ number_format($ledger['locked'], 2) }}</p>

        <p><b>Transfer ID:</b> {{ $payoutRequest->transfer_id ?? 'NA' }}</p>
        <p><b>Stripe Payout ID:</b> {{ $payoutRequest->stripe_payout_id ?? 'NA' }}</p>

        <p><b>Admin Note:</b> {{ $payoutRequest->admin_note ?? 'NA' }}</p>
        <p><b>Rejection Reason:</b> {{ $payoutRequest->rejection_reason ?? 'NA' }}</p>

        <p><b>Processed By:</b> {{ $payoutRequest->processedBy->name ?? 'NA' }}</p>
        <p><b>Processed At:</b> {{ $payoutRequest->processed_at?->format('d M Y, h:i A') ?? 'NA' }}</p>

        <p><b>Requested At:</b> {{ $payoutRequest->created_at->format('d M Y, h:i A') }}</p>

        @if ($payoutRequest->status === 'pending')
            <div class="mt-4">
                <button class="btn btn-success approve-payout" data-id="{{ $payoutRequest->id }}">
                    <i class="ri-check-line me-1"></i> Approve
                </button>
                <button class="btn btn-danger reject-payout" data-id="{{ $payoutRequest->id }}">
                    <i class="ri-close-line me-1"></i> Reject
                </button>
            </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
    <script>
    $(document).on('click', '.approve-payout', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Approve this payout?",
            text: "This will transfer platform funds to the provider's Stripe account.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, approve",
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/payout-requests/${id}/approve`,
                    type: "POST",
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        Swal.fire("Approved!", res.message, "success").then(() => location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire("Error", xhr.responseJSON?.message ?? "Something went wrong", "error");
                    }
                });
            }
        });
    });

    $(document).on('click', '.reject-payout', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Reject this payout?",
            input: "textarea",
            inputLabel: "Reason for rejection",
            inputPlaceholder: "Enter reason...",
            showCancelButton: true,
            confirmButtonText: "Reject",
            inputValidator: (value) => !value && "A reason is required."
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/payout-requests/${id}/reject`,
                    type: "POST",
                    data: { _token: '{{ csrf_token() }}', reason: res.value },
                    success: function (res) {
                        Swal.fire("Rejected!", res.message, "success").then(() => location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire("Error", xhr.responseJSON?.message ?? "Something went wrong", "error");
                    }
                });
            }
        });
    });
    </script>
@endsection