@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Master Data Relationship Export</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Filter Section -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Filter & Relationship Selection</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="company_filter" class="form-label">Company Filter</label>
                                <select id="company_filter" class="form-select">
                                    <option value="">All Companies</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->company_code }}"
                                            {{ $userCompany == $company->company_code && $userCompany != 'A000' ? 'selected' : '' }}>
                                            {{ $company->company_code }} - {{ $company->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="periode_filter" class="form-label">Periode Filter</label>
                                <select id="periode_filter" class="form-select">
                                    <option value="">Latest Periode</option>
                                    @foreach ($periodes as $periode)
                                        <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="start_point" class="form-label">Start Point <span
                                        class="text-danger">*</span></label>
                                <select id="start_point" class="form-select" required>
                                    <option value="">Select Start Point</option>
                                    <option value="user">User</option>
                                    <option value="job_role">Job Role</option>
                                    <option value="composite_role">Composite Role</option>
                                    <option value="single_role">Single Role</option>
                                    <option value="tcode">Tcode</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="end_point" class="form-label">End Point <span
                                        class="text-danger">*</span></label>
                                <select id="end_point" class="form-select" disabled required>
                                    <option value="">Select End Point</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Info:</strong>
                                    <ul class="mb-0 small">
                                        <li>Select <strong>Start Point</strong> and <strong>End Point</strong> to define the
                                            data relationship chain</li>
                                        <li>Both forward (User → Tcode) and reverse (Tcode → User) relationships are
                                            supported</li>
                                        <li>Company and Periode filters are optional (leave blank for all data)</li>
                                        <li>Click <strong>Preview</strong> to see the data, then <strong>Export</strong> to
                                            download Excel</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button id="btn_preview" class="btn btn-primary" disabled>
                                    <i class="bi bi-eye"></i> Preview Data
                                </button>
                                <button id="btn_export" class="btn btn-success" disabled>
                                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                                </button>
                                <button id="btn_reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div id="preview_section" style="display: none;">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Data Preview</h6>
                        </div>
                        <div class="card-body">
                            <div id="loading_indicator" class="text-center py-5" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading data...</p>
                            </div>

                            <div id="table_container">
                                <table id="preview_table" class="table table-striped table-bordered table-hover w-100">
                                    <thead id="table_header">
                                        <!-- Dynamic columns will be inserted here -->
                                    </thead>
                                    <tbody>
                                        <!-- DataTables content -->
                                    </tbody>
                                </table>
                            </div>
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
            let previewTable = null;
            let currentColumns = [];
            const MAX_EXPORT_ROWS = {{ $maxExportRows }};

            // Relationship hierarchy for validation
            const hierarchy = ['user', 'job_role', 'composite_role', 'single_role', 'tcode'];

            // Valid end points for each start point (bidirectional)
            const validRelationships = {
                'user': ['job_role', 'composite_role', 'single_role', 'tcode'],
                'job_role': ['user', 'composite_role', 'single_role', 'tcode'],
                'composite_role': ['user', 'job_role', 'single_role', 'tcode'],
                'single_role': ['user', 'job_role', 'composite_role', 'tcode'],
                'tcode': ['user', 'job_role', 'composite_role', 'single_role']
            };

            // Start point change handler
            $('#start_point').on('change', function() {
                const startPoint = $(this).val();
                const endPointDropdown = $('#end_point');

                endPointDropdown.empty().append('<option value="">Select End Point</option>');

                if (startPoint) {
                    const validEnds = validRelationships[startPoint] || [];

                    const options = [{
                            value: 'user',
                            label: 'User'
                        },
                        {
                            value: 'job_role',
                            label: 'Job Role'
                        },
                        {
                            value: 'composite_role',
                            label: 'Composite Role'
                        },
                        {
                            value: 'single_role',
                            label: 'Single Role'
                        },
                        {
                            value: 'tcode',
                            label: 'Tcode'
                        }
                    ];

                    options.forEach(opt => {
                        if (validEnds.includes(opt.value) && opt.value !== startPoint) {
                            endPointDropdown.append(
                                `<option value="${opt.value}">${opt.label}</option>`);
                        }
                    });

                    endPointDropdown.prop('disabled', false);
                } else {
                    endPointDropdown.prop('disabled', true);
                }

                $('#btn_preview').prop('disabled', true);
                $('#btn_export').prop('disabled', true);
                $('#preview_section').hide();
            });

            // End point change handler
            $('#end_point').on('change', function() {
                const startPoint = $('#start_point').val();
                const endPoint = $(this).val();

                if (startPoint && endPoint) {
                    $('#btn_preview').prop('disabled', false);
                } else {
                    $('#btn_preview').prop('disabled', true);
                }

                $('#btn_export').prop('disabled', true);
                $('#preview_section').hide();
            });

            // Preview button handler - SINGLE AJAX CALL
            $('#btn_preview').on('click', function() {
                const startPoint = $('#start_point').val();
                const endPoint = $('#end_point').val();
                const companyId = $('#company_filter').val();
                const periodeId = $('#periode_filter').val();

                if (!startPoint || !endPoint) {
                    alert('Please select both Start Point and End Point');
                    return;
                }

                $('#loading_indicator').show();
                $('#table_container').hide();
                $('#preview_section').show();

                if (previewTable) {
                    previewTable.destroy();
                    $('#preview_table thead').empty();
                    $('#preview_table tbody').empty();
                }

                // Make single AJAX call to get columns and initialize DataTable
                $.ajax({
                    url: '{{ route('master-data-export.preview') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        start_point: startPoint,
                        end_point: endPoint,
                        company_id: companyId,
                        periode_id: periodeId,
                        length: 10,
                        start: 0,
                        draw: 1
                    },
                    success: function(response) {
                        currentColumns = response.columns || [];

                        // Build column definitions
                        const dtColumns = [{
                            data: 'row_number',
                            name: 'row_number',
                            title: 'No',
                            orderable: false,
                            searchable: false,
                            width: '50px'
                        }];

                        // Add dynamic columns based on path
                        currentColumns.forEach(col => {
                            dtColumns.push({
                                data: col.name, // Direct access (no nesting)
                                name: col.name,
                                title: col.title,
                                defaultContent: '-',
                                orderable: false
                            });
                        });

                        // Build table header
                        let headerHtml = '<tr>';
                        dtColumns.forEach(col => {
                            headerHtml += `<th>${col.title}</th>`;
                        });
                        headerHtml += '</tr>';
                        $('#preview_table thead').html(headerHtml);

                        // Initialize DataTable
                        previewTable = $('#preview_table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: '{{ route('master-data-export.preview') }}',
                                type: 'POST',
                                data: function(d) {
                                    d._token = '{{ csrf_token() }}';
                                    d.start_point = startPoint;
                                    d.end_point = endPoint;
                                    d.company_id = companyId;
                                    d.periode_id = periodeId;
                                }
                            },
                            columns: dtColumns,
                            pageLength: 25,
                            lengthMenu: [
                                [10, 25, 50, 100, 500],
                                [10, 25, 50, 100, 500]
                            ],
                            order: [
                                [0, 'asc']
                            ],
                            responsive: true,
                            drawCallback: function(settings) {
                                const api = this.api();
                                const recordsTotal = api.page.info().recordsTotal;

                                if (recordsTotal > MAX_EXPORT_ROWS) {
                                    if ($('#export_warning').length === 0) {
                                        $('#preview_section').prepend(`
                                            <div id="export_warning" class="alert alert-warning alert-dismissible fade show">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <strong>Warning:</strong> Preview contains ${recordsTotal.toLocaleString()} rows. 
                                                Export will be limited to first ${MAX_EXPORT_ROWS.toLocaleString()} rows. 
                                                Please apply filters to reduce data size.
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        `);
                                    }
                                }
                            }
                        });

                        $('#loading_indicator').hide();
                        $('#table_container').show();
                        $('#btn_export').prop('disabled', false);
                    },
                    error: function(xhr) {
                        $('#loading_indicator').hide();
                        console.error('Preview error:', xhr.responseJSON);
                        alert('Error loading preview: ' + (xhr.responseJSON?.message ||
                            'Unknown error'));
                    }
                });
            });

            // Export button handler
            $('#btn_export').on('click', function() {
                const startPoint = $('#start_point').val();
                const endPoint = $('#end_point').val();
                const companyId = $('#company_filter').val();
                const periodeId = $('#periode_filter').val();

                if (!startPoint || !endPoint) {
                    alert('Please preview data first');
                    return;
                }

                const params = new URLSearchParams({
                    start_point: startPoint,
                    end_point: endPoint,
                    company_id: companyId || '',
                    periode_id: periodeId || ''
                });

                window.location.href = '{{ route('master-data-export.export') }}?' + params.toString();
            });

            // Reset button handler
            $('#btn_reset').on('click', function() {
                $('#company_filter').val('');
                $('#periode_filter').val('');
                $('#start_point').val('').trigger('change');
                $('#end_point').val('').prop('disabled', true);
                $('#btn_preview').prop('disabled', true);
                $('#btn_export').prop('disabled', true);
                $('#preview_section').hide();

                if (previewTable) {
                    previewTable.destroy();
                    previewTable = null;
                }

                $('#export_warning').remove();
            });
        });
    </script>
@endsection
