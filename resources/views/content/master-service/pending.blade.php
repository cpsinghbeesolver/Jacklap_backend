@extends('layouts.contentNavbarLayout')

@section('title', 'Master Services')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between">
        <h5 class="card-header">Pending Services</h5>

        {{--  <div class="m-3">
            <a href="{{ route('master-service.create') }}" class="btn btn-primary btn-sm">
                <i class="ri-add-line"></i> Add Service
            </a>
        </div>  --}}
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
        ajax: "{{ route('pending-service') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'category' },
            { data: 'type' },
            { data: 'status', orderable: false },
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

    $(document).on('change', '.toggle-status', function () {
        let id = $(this).data('id');
        let status = $(this).is(':checked') ? 1 : 0;
    
        $.ajax({
            url: "{{ route('master-service.toggle-status') }}",
            type: "POST",
            data: {
                id: id,
                status: status,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {
                toastr.success(res.message);
                table.ajax.reload();
            }
        });
    });
</script>
@endsection