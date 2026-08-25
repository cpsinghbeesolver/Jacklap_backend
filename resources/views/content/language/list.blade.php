@extends('layouts.contentNavbarLayout')
@section('title', 'Language')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Language</h5>
        <a href="{{ route('create-language') }}" class="btn btn-sm btn-primary">
            <i class="ri-add-line"></i> Add Language
        </a>
    </div>

    <div class="card-body">
        <table class="table" id="language-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Language</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@section('scripts')
    <script>
    let table = $('#language-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('language') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'actions', orderable: false }
        ]
    });

    // delete
    $(document).on('click', '.delete-language', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            icon: "warning",
            showCancelButton: true,
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/languages/delete/${id}`,
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