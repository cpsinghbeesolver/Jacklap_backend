@extends('layouts.contentNavbarLayout')
@section('title', 'Bookings')
@section('content')

<div class="card">
    <h5 class="card-header fw-bold">Bookings</h5>

    <div class="card-body">
        <table class="table" id="booking-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Booking #</th>
                    <th>Customer</th>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Payable Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let table = $('#booking-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('booking-list') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'booking_number' },
            { data: 'customer', orderable: false },
            { data: 'provider', orderable: false },
            { data: 'status', orderable: false },
            { data: 'payable_amount' },
            { data: 'start_datetime' },
            { data: 'actions', orderable: false }
        ]
    });

    $(document).on('click', '.delete-booking', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            icon: "warning",
            showCancelButton: true,
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/booking/delete/${id}`,
                    type: "DELETE",
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        Swal.fire("Deleted!", res.message, "success");
                        table.ajax.reload();
                    }
                });
            }
        });
    });
</script>
@endsection