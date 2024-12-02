@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Single Roles</h1>

        <!-- Trigger button for Create Modal -->
        <button type="button" id="triggerCreateModal" class="btn btn-primary mb-3">
            Create New Single Role
        </button>

        <!-- Status Messages -->
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <!-- Table for displaying Single Roles -->
        <table id="single_roles_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>Perusahaan</th>
                    <th>Single Role</th>
                    <th>Deskripsi</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modals -->
    <div id="modalContainer"></div> <!-- Placeholder for loading modals dynamically -->
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#single_roles_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('single-roles.data') }}", // AJAX route
                columns: [{
                        data: 'company.name',
                        name: 'company.name',
                        title: 'Perusahaan'
                    },
                    {
                        data: 'nama',
                        name: 'nama',
                        title: 'Single Role'
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi',
                        title: 'Deskripsi'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        title: 'Actions'
                    },
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
            });

            // Load and show the Create Modal when the button is clicked
            $('#triggerCreateModal').on('click', function() {
                $.get('{{ route('single-roles.create') }}', function(data) {
                    $('#modalContainer').html(data);
                    $('#createSingleRoleModal').modal('show');
                });
            });

            // Load and show the Edit Modal
            $(document).on('click', '.edit-single-role', function() {
                var roleId = $(this).data('id'); // Get the ID from the button
                $.ajax({
                    url: '/single-roles/' + roleId + '/edit',
                    method: 'GET',
                    success: function(data) {
                        // Open the modal
                        $('#modalContainer').html(data);
                        $('#editSingleRoleModal').modal('show');
                    },
                    error: function() {
                        alert('Failed to fetch data for editing.');
                    }
                });
            });

            // Load and show the Show Modal
            $(document).on('click', '.show-single-role', function() {
                const singleRoleId = $(this).data('id');
                $.get(`/single-roles/${singleRoleId}`, function(data) {
                    $('#modalContainer').html(data);
                    $('#showSingleRoleModal').modal('show');
                });
            });

            // Close modal handler
            $(document).on('click', '.close', function() {
                $('.modal').modal('hide');
            });

            // Handle create form submission via AJAX
            $('#createSingleRoleModal').on('submit', 'form', function(event) {
                event.preventDefault();
                let form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#single_roles_table tbody').append(response
                                .html); // Add the new row
                            $('#createSingleRoleModal').modal('hide');
                            form[0].reset(); // Reset form
                        }
                    },
                    error: function(xhr) {
                        alert('An error occurred. Please try again.');
                    }
                });
            });

            // Handle edit form submission via AJAX
            $(document).on('submit', '#editSingleRoleForm', function(e) {
                e.preventDefault(); // Prevent default form submission

                var form = $(this);
                var actionUrl = form.attr('action');
                var formData = form.serialize() +
                    '&_method=PUT'; // Add _method=PUT to the form data for spoofing

                console.log('Submitting to URL:', actionUrl);
                console.log('Form Data (with method spoofing):', formData);

                $.ajax({
                    url: actionUrl,
                    method: 'POST', // Use POST method for spoofing
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            // Get the row ID from the form or modal input field
                            var rowId = $('#editSingleRoleModal input[name="id"]').val();
                            var existingRow = $('tr[data-id="' + rowId + '"]');

                            if (existingRow.length > 0) {
                                // Replace the existing row with the new HTML returned from the server
                                console.log('Replacing row with ID:', rowId);
                                existingRow.replaceWith(response.html);
                                bindRowEvents(); // Rebind events for new elements if necessary
                            } else {
                                console.error(
                                    'Row with specified ID not found. Adding new row.');
                                // Optionally append the new row if it doesn't exist
                                $('#single_roles_table tbody').append(response.html);
                            }

                            // Hide the modal after a successful update
                            $('#editSingleRoleModal').modal('hide');
                        } else {
                            alert('Failed to update the role.');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error Response:', xhr.responseText);
                        alert('Failed to update the role.');
                    }
                });
            });

            // Optional: Rebind events function (if needed)
            function bindRowEvents() {
                $(document).off('click', '.edit-single-role'); // Remove previous bindings to avoid duplicates
                $(document).on('click', '.edit-single-role', function() {
                    // Your existing logic for editing goes here
                });
            }

        });
    </script>
@endsection
