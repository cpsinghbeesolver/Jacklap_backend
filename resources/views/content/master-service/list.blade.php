@extends('layouts.contentNavbarLayout')

@section('title', 'Master Services')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between">
        <h5 class="card-header">Master Services</h5>

        <div class="m-3">
            <a href="{{ route('master-service.create') }}" class="btn btn-primary btn-sm">
                <i class="ri-add-line"></i> Add Service
            </a>
        </div>
    </div>

    <div class="card-body">
        <table class="table" id="service-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Price Limit</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
let table = $('#service-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('master-service') }}",
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'name' },
        { data: 'category' },
        { data: 'type' },
        { data: 'status', orderable: false },
        { data: 'price_limit', orderable: false },
        { data: 'actions', orderable: false }
    ]
});

// delete
$(document).on('click', '.delete-service', function () {
    let id = $(this).data('id');
    Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
    }).then((res) => {
        if (res.isConfirmed) {
            $.ajax({
                url: `/master-service/delete/${id}`,
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