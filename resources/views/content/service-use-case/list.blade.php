@extends('layouts.contentNavbarLayout')
@section('title', 'Service Use Cases')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Service Use Cases</h5>
        <a href="{{ route('create-service-use-case') }}" class="btn btn-sm btn-primary">
            <i class="ri-add-line"></i> Add Use Case
        </a>
    </div>

    <div class="card-body">
        <table class="table" id="service-use-case-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Full Day</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@section('scripts')
    <script>
    let table = $('#service-use-case-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('service-use-case') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title' },
            { data: 'is_full_day', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });

    // delete
    $(document).on('click', '.delete-service-use-case', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            icon: "warning",
            showCancelButton: true,
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/service-use-case/delete/${id}`,
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