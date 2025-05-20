@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-3">
            <h4>Dashboard User Detail</h4>
            <a href="{{ route('user-detail.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </div>

        <div id="user-table"></div>

        <!-- Show Modal -->
        <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="showModalLabel">User Detail Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-light" width="30%">NIK</th>
                                        <td id="show-nik"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Name</th>
                                        <td id="show-nama"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Email</th>
                                        <td id="show-email"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Company</th>
                                        <td id="show-company"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-light" width="30%">Direktorat</th>
                                        <td id="show-direktorat"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Kompartemen</th>
                                        <td id="show-kompartemen"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Departemen</th>
                                        <td id="show-departemen"></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Created At</th>
                                        <td id="show-created-at"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning edit-btn">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button type="button" class="btn btn-danger delete-btn">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let table = new Tabulator("#user-table", {
            ajaxURL: "{{ route('user-detail.getData') }}",
            layout: "fitColumns",
            pagination: "local",
            paginationSize: 10,
            paginationSizeSelector: [10, 15, 20, 30, 50],
            columns: [{
                    title: "Name",
                    field: "nama",
                    headerFilter: "input"
                },
                {
                    title: "NIK",
                    field: "nik",
                    headerFilter: "input"
                },
                {
                    title: "Company",
                    field: "company",
                    headerFilter: "input"
                },
                {
                    title: "Direktorat",
                    field: "direktorat",
                    headerFilter: "input"
                },
                {
                    title: "Kompartemen",
                    field: "kompartemen",
                    headerFilter: "input"
                },
                {
                    title: "Departemen",
                    field: "departemen",
                    headerFilter: "input"
                },
                {
                    title: "Email",
                    field: "email",
                    headerFilter: "input"
                },
                {
                    title: "Actions",
                    formatter: function(cell) {
                        const data = cell.getRow().getData();
                        return `
                    <button class="btn btn-sm btn-info show-detail" data-id="${data.id}">
                        <i class="bi bi-eye"></i>
                    </button>
                    <a href="/user-detail/${data.id}/edit" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i>
                    </a>`;
                    },
                    hozAlign: "center",
                    width: 100,
                    headerSort: false
                }
            ],
            initialSort: [{
                column: "nama",
                dir: "asc"
            }],
        });

        // Show Modal Handler
        $(document).on('click', '.show-detail', function() {
            const id = $(this).data('id');

            $.ajax({
                url: `/user-detail/${id}`,
                method: 'GET',
                success: function(response) {
                    $('#show-nik').text(response.nik);
                    $('#show-nama').text(response.nama);
                    $('#show-email').text(response.email);
                    $('#show-company').text(response.company_data?.nama || '-');
                    $('#show-direktorat').text(response.direktorat || '-');
                    $('#show-kompartemen').text(response.kompartemen?.nama || '-');
                    $('#show-departemen').text(response.departemen?.nama || '-');

                    // Set up edit and delete buttons
                    $('.edit-btn').attr('href', `/user-detail/${id}/edit`);
                    $('.delete-btn').data('id', id);

                    $('#showModal').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load user details'
                    });
                }
            });
        });

        // Delete Handler
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/user-detail/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#showModal').modal('hide');
                                table.replaceData();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete user detail'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
