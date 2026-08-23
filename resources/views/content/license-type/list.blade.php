@extends('layouts.contentNavbarLayout')
@section('title', 'License Types')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">License Types</h5>
        <a href="{{ route('create-license-type') }}" class="btn btn-sm btn-primary">
            <i class="ri-add-line"></i> Add License Type
        </a>
    </div>

    <div class="card-body">
        <table class="table" id="license-type-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@section('scripts')
    <script>
    let table = $('#license-type-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('license-type') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'description' },
            { data: 'actions', orderable: false }
        ]
    });

    // delete
    $(document).on('click', '.delete-license-type', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            icon: "warning",
            showCancelButton: true,
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/license-type/delete/${id}`,
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