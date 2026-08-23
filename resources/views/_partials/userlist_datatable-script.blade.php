<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/sweetalert.js') }}"></script>

<script>
let table;



$(document).ready(function () {

    if (!$('#users-table').length) {
        return; // ❗ prevents JS crash
    }

    table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('user-list') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'avatar', orderable: false, searchable: false },
            { data: 'email', orderable: false },
            { data: 'phone', orderable: false },
            { data: 'role', orderable: false },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });
});

// Delete User
$('.table').on('click', '.delete-user', function () {
    const userId = $(this).data('id');

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url("user-list/delete") }}/${userId}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    Swal.fire("Deleted!", response.message, "success");
                    table.ajax.reload();
                }
            });
        }
    });
});
</script>
