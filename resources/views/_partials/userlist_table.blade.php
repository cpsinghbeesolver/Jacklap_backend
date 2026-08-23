<div class="card">
    <h5 class="card-header text-center fw-bold">
        {{ ucfirst(request('role', 'User')) }} List
    </h5>
    <div class="card-body">
        <table class="table table-bordered table-hover align-middle" id="users-table">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@section('scripts')
    <script>
        let table;
        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const role = urlParams.get('role') || '';

            if (!$('#users-table').length) {
                return; 
            }

            table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('user-list') }}",
                    data: function (d) {
                        d.role = role;
                    }
                },
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
    @if (session('success'))
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('success') }}",
            confirmButtonText: 'OK'
        });
        </script>
    @endif
@endsection


