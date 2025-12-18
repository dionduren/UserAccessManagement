@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Audit Trail Logs</h1>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label for="activity_type" class="form-label">Activity Type</label>
                        <select class="form-select" id="activity_type" name="activity_type">
                            <option value="">All Activities</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="create">Create</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="model_type" class="form-label">Model Type</label>
                        <input type="text" class="form-control" id="model_type" name="model_type"
                            placeholder="e.g., Company">
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" id="applyFilter">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetFilter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card">
            <div class="card-body">
                <table id="auditTrailTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Model</th>
                            <th>Model ID</th>
                            <th>IP Address</th>
                            <th>Route</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for viewing changes -->
    <div class="modal fade" id="changesModal" tabindex="-1" aria-labelledby="changesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changesModalLabel">Changes Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Before</h6>
                            <pre id="beforeData" class="bg-light p-3 rounded"></pre>
                        </div>
                        <div class="col-md-6">
                            <h6>After</h6>
                            <pre id="afterData" class="bg-light p-3 rounded"></pre>
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
            const table = $('#auditTrailTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('audit-trails.data') }}',
                    data: function(d) {
                        d.activity_type = $('#activity_type').val();
                        d.model_type = $('#model_type').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'id',
                        width: '50px'
                    },
                    {
                        data: 'logged_at'
                    },
                    {
                        data: 'user_name'
                    },
                    {
                        data: 'activity_type',
                        render: function(data) {
                            const badges = {
                                'login': 'success',
                                'logout': 'secondary',
                                'create': 'primary',
                                'update': 'warning',
                                'delete': 'danger'
                            };
                            return `<span class="badge bg-${badges[data] || 'info'}">${data}</span>`;
                        }
                    },
                    {
                        data: 'model_name'
                    },
                    {
                        data: 'model_id'
                    },
                    {
                        data: 'ip_address'
                    },
                    {
                        data: 'route'
                    },
                    {
                        data: 'changes',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                pageLength: 25
            });

            // Apply filter
            $('#applyFilter').on('click', function() {
                table.ajax.reload();
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                table.ajax.reload();
            });

            // View changes modal
            $(document).on('click', '.view-changes', function() {
                const before = $(this).data('before');
                const after = $(this).data('after');

                $('#beforeData').text(JSON.stringify(before, null, 2));
                $('#afterData').text(JSON.stringify(after, null, 2));

                const modal = new bootstrap.Modal(document.getElementById('changesModal'));
                modal.show();
            });
        });
    </script>
@endsection
