@extends('layouts.contentNavbarLayout')
@section('title', 'Payout Requests')
@section('content')

<div class="card">
    <h5 class="card-header text-center fw-bold">Payout Requests</h5>

    <div class="card-body">
        <table class="table" id="payout-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Provider</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@section('scripts')
    <script>
    let table = $('#payout-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('payout-list') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'provider' },
            { data: 'amount', orderable: false },
            { data: 'status', orderable: false },
            { data: 'created_at' },
            { data: 'actions', orderable: false }
        ]
    });

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
                        Swal.fire("Approved!", res.message, "success");
                        table.ajax.reload();
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
                        Swal.fire("Rejected!", res.message, "success");
                        table.ajax.reload();
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