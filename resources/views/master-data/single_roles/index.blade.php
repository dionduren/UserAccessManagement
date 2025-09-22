@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- General Error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Status Messages -->
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Master Data Single Roles</h2>
            </div>
            <div class="card-body">

                <!-- Trigger button for Create Modal -->
                @if (isset($userCompanyCode) && $userCompanyCode == 'A000')
                    <button type="button" id="triggerCreateModal" class="btn btn-primary mb-3">
                        Buat Single Role Baru
                    </button>
                @endif


                <!-- Table for displaying Single Roles -->
                <table id="single_roles_table" class="table table-bordered table-striped table-hover cell-border mt-3">
                    <thead>
                        <tr>
                            <th>Single Role</th>
                            <th>Deskripsi</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Modals -->
            {{-- <div id="modalContainer"></div> <!-- Placeholder for loading modals dynamically --> --}}

            <!-- Placeholder for modals -->
            <div class="modal fade" id="singleRoleModal" tabindex="-1" aria-labelledby="singleRoleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="singleRoleModalLabel"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="singleRoleModalBody">
                            <!-- Content for create, edit, or show details will be loaded dynamically -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Initialize DataTable
            const table = $('#single_roles_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/single-roles/data',
                },
                columns: [{
                        data: 'nama',
                        name: 'nama',
                        title: 'Single Role'
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi',
                        title: 'Deskripsi',
                        width: '50%'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        title: 'Actions',
                        width: '10%'
                    },
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
            });

            // Function to load modal content dynamically
            function loadModalContent(url, title) {
                $('#singleRoleModalLabel').text(title); // Set modal title
                $('#singleRoleModalBody').html(
                    '<div class="text-center">Loading...</div>'); // Temporary loading state
                $('#singleRoleModal').modal('show'); // Show the modal

                $.get(url, function(data) {
                    $('#singleRoleModalBody').html(data); // Populate modal-body with received content
                }).fail(function() {
                    alert('Failed to load data. Please try again.');
                });
            }

            // Handle Create Modal
            $('#triggerCreateModal').on('click', function() {
                loadModalContent('{{ route('single-roles.create') }}', 'Create Single Role');
            });

            // Handle Edit Modal
            $(document).on('click', '.edit-single-role', function() {
                const roleId = $(this).data('id');
                const url = `/single-roles/${roleId}/edit`;
                loadModalContent(url, 'Edit Single Role');
            });

            // Handle Show Details Modal
            $(document).on('click', '.show-single-role', function() {
                const roleId = $(this).data('id');
                const url = `/single-roles/${roleId}`;
                loadModalContent(url, 'Single Role Details');
            });

            // Close modal when the close button is clicked
            $(document).on('click', '.close', function() {
                $('#singleRoleModal').modal('hide');
            });

            // Optionally, handle AJAX form submission inside the modal dynamically (Create/Edit)
            $(document).on('submit', 'form.ajax-modal-form', function(event) {
                event.preventDefault();
                const form = $(this);
                const actionUrl = form.attr('action');
                const method = form.attr('method');
                const formData = form.serialize();

                $.ajax({
                    url: actionUrl,
                    method: method,
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#single_roles_table').DataTable().ajax
                                .reload(); // Reload DataTable
                            $('#singleRoleModal').modal('hide'); // Close modal
                            // alert(response.message); // Show success message
                        } else {
                            alert('Failed to save changes.');
                        }
                    },
                    error: function(xhr) {
                        alert('An error occurred. Please try again.');
                    }
                });
            });

            // Optional: Rebind events function (if needed)
            // function bindRowEvents() {
            //     $(document).off('click', '.edit-single-role'); // Remove previous bindings to avoid duplicates
            //     $(document).on('click', '.edit-single-role', function() {
            //         // Your existing logic for editing goes here
            //     });
            // }

            $(document).on('click', '.delete-single-role', function(e) {
                e.preventDefault();
                const button = $(this);
                const url = button.data('url');

                Swal.fire({
                    title: 'Menghapus Data Ini?',
                    text: "Anda akan menghapus data ini secara permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit a hidden form dynamically
                        const form = $('<form>', {
                            method: 'POST',
                            action: url
                        });

                        const token = $('meta[name="csrf-token"]').attr('content');

                        form.append($('<input>', {
                            type: 'hidden',
                            name: '_token',
                            value: token
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: '_method',
                            value: 'DELETE'
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                });
            });


        });
    </script>
@endsection
