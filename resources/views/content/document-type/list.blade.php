@extends('layouts.contentNavbarLayout')

@section('title', 'Document Types')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between">
        <h5 class="card-header">Document Types</h5>

        <div class="m-3">
            <a href="{{ route('document-type.create') }}" class="btn btn-primary btn-sm">
                <i class="ri-add-line"></i> Add Document Type
            </a>
        </div>
    </div>

    <div class="card-body">
        <table class="table" id="type-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Total Documents</th>
                    <th>Required</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('scripts')

<script>
let table = $('#type-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('document-type') }}",
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'name' },
        { data: 'total_documents',  orderable: false },
        { data: 'is_required',      orderable: false },
        { data: 'actions',          orderable: false }
    ]
});

$(document).on('click', '.delete-type', function () {
    let id = $(this).data('id');

    Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
    }).then((res) => {
        if (res.isConfirmed) {
            $.ajax({
                url: `/document-type/delete/${id}`,
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