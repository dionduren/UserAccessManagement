@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Master Data Periode</h1>

        <a href="{{ route('periode.create') }}" target="_blank" class="btn btn-outline-primary mb-3">
            {{-- <a class="btn btn-outline-secondary mb-3" disabled> --}}
            <i class="bi bi-plus"></i> Buat Periode Baru
        </a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <table id="periode_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Definisi</th>
                    <th>Tanggal Pembuatan</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let masterData = {}; // Store parsed JSON for efficient lookups

            let periodeTable = $('#periode_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('periode.index') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'definisi',
                        name: 'definisi'
                    },
                    {
                        data: 'tanggal_create_periode',
                        name: 'tanggal_create_periode'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                columnDefs: [{
                    targets: [0],
                    visible: false
                }],
                order: [
                    [2, 'asc']
                ]
            });

        });

        // ✅ SweetAlert2 Delete Confirmation
        // function deletePeriode(id) {
        //     Swal.fire({
        //         title: "Apakah anda yakin?",
        //         text: "Anda tidak bisa mengembalikan data yang dihapus!",
        //         icon: "warning",
        //         showCancelButton: true,
        //         confirmButtonColor: "#d33",
        //         cancelButtonColor: "#3085d6",
        //         confirmButtonText: "Ya, hapus!",
        //         cancelButtonText: "Tidak Jadi"
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             $.ajax({
        //                 url: '/master_data/' + id,
        //                 type: 'DELETE',
        //                 data: {
        //                     _token: '{{ csrf_token() }}'
        //                 },
        //                 success: function(response) {
        //                     Swal.fire({
        //                         title: "Deleted!",
        //                         text: response.success,
        //                         icon: "success",
        //                         showConfirmButton: false,
        //                         timer: 1500
        //                     });

        //                     // Find the row to remove
        //                     let row = $('#user_nik_table').DataTable().row($(
        //                         `button[data-id="${id}"]`).parents('tr'));

        //                     // Remove the row and redraw the table
        //                     row.remove().draw();
        //                 },
        //                 error: function(xhr) {
        //                     Swal.fire({
        //                         title: "Error!",
        //                         text: "Failed to delete record.",
        //                         icon: "error"
        //                     });
        //                 }
        //             });
        //         }
        //     });
        // }
    </script>
@endsection
