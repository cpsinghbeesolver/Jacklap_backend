@extends('layouts.contentNavbarLayout')

@section('title', 'Pages')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between">
        <h5 class="card-header">Pages</h5>

        <div class="m-3">
            <a href="{{ route('page.create') }}" class="btn btn-primary btn-sm">
                <i class="ri-add-line"></i> Add Page
            </a>
        </div>
    </div>

    <div class="card-body">
        <table class="table" id="page-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Slug</th>
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
let table = $('#page-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('page') }}",
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'title' },
        { data: 'slug',    orderable: false },
        { data: 'is_active', orderable: false },
        { data: 'actions',   orderable: false }
    ]
});

$(document).on('click', '.delete-page', function () {
    let id = $(this).data('id');

    Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
    }).then((res) => {
        if (res.isConfirmed) {
            $.ajax({
                url: `/page/delete/${id}`,
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