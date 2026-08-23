@extends('layouts.contentNavbarLayout')
@section('title', 'Service Categories')
@section('content')

<div class="card">
    <h5 class="card-header text-center fw-bold">Service Categories</h5>

    <div class="card-body">
        <table class="table" id="category-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
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
    let table = $('#category-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('category-list') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image', orderable: false },
            { data: 'name' },
            { data: 'price' },
            { data: 'status', orderable: false },
            { data: 'actions', orderable: false }
        ]
    });

    // delete
    $(document).on('click', '.delete-category', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            icon: "warning",
            showCancelButton: true,
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/category/delete/${id}`,
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