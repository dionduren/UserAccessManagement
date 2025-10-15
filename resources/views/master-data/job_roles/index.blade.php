@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Master Data Job Roles</h2>
            </div>
            <div class="card-body">

                <a href="{{ route('job-roles.create') }}" class="btn btn-primary mb-3">Buat Job Role</a>
                <a href="#" id="downloadFlaggedBtn" class="btn btn-outline-danger mb-3 ms-2">
                    <i class="bi bi-download"></i> Download Flagged Data
                </a>

                @can('Super Admin')
                    <a href="#" id="export-job-userid-btn" class="btn btn-success mb-3 ms-2">
                        <i class="bi bi-file-earmark-excel"></i> Export Job Role User IDs
                    </a>

                    <select id="periodeDropdown" class="3 mb-3">
                        <option value="">-- Pilih Periode Export --</option>
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}">
                                {{ $periode->periode ?? "Periode {$periode->id}" }}
                                {{ $periode->definisi ? '- ' . $periode->definisi : '' }}
                            </option>
                        @endforeach
                    </select>
                @endcan

                @can('Super User')
                    <button id="bulkDeleteButton" class="btn btn-danger mb-3 d-none">Hapus Terpilih</button>
                @endcan

                <!-- Success Message -->
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif


                <!-- Dropdown for Company Selection -->
                <div class="form-group mb-3">
                    <label for="companyDropdown">Pilih Perusahaan</label>
                    <select id="companyDropdown" class="form-control">
                        <option value="">-- Semua Perusahaan --</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown for Kompartemen Selection -->
                <div class="form-group mb-3">
                    <label for="kompartemenDropdown">Pilih Kompartemen</label>
                    <select id="kompartemenDropdown" class="form-control" disabled>
                        <option value="">-- Semua Kompartemen --</option>
                    </select>
                </div>

                <!-- Dropdown for Departemen Selection -->
                <div class="form-group mb-3">
                    <label for="departemenDropdown">Pilih Departemen</label>
                    <select id="departemenDropdown" class="form-control" disabled>
                        <option value="">-- Semua Departemen --</option>
                    </select>
                </div>

                <!-- Table to display Job Roles -->
                <table id="jobRolesTable" class="table table-bordered table-striped table-hover mt-3">
                    <thead>
                        {{-- DataTables will render the header titles from JS.
                             We append a filters row programmatically in initComplete. --}}
                    </thead>
                    <tbody></tbody>
                </table>

            </div>

            <!-- Modal for Job Role Details -->
            <div class="modal fade" id="showJobRoleModal" tabindex="-1" aria-labelledby="showJobRoleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="showJobRoleModalLabel">Job Role Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            {{-- Bootstrap 5 --}}
                        </div>
                        <div class="modal-body" id="modal-job-role-details">
                            <!-- Job Role details will be dynamically loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Flagged Info and Change Flagged Status -->
            <div class="modal fade" id="flaggedJobRoleModal" tabindex="-1" aria-labelledby="flaggedJobRoleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="flaggedJobRoleModalLabel">Flagged Info</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="modal-flagged-job-role-details">
                            <!-- Flagged info will be loaded here -->
                            <div class="text-center">
                                <span class="spinner-border" role="status"></span>
                            </div>
                        </div>
                        <div class="modal-footer d-none" id="flagged-job-role-actions">
                            <form id="flaggedJobRoleForm" method="POST">
                                @csrf
                                <input type="hidden" name="job_role_id" id="flagged-job-role-id" value="">
                                <div class="form-group">
                                    <label for="flagged-status">Flagged Status</label>
                                    <select class="form-control" name="flagged" id="flagged-status">
                                        <option value="1">Flagged</option>
                                        <option value="0">Not Flagged</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="flagged-keterangan">Keterangan</label>
                                    <textarea class="form-control" name="keterangan" id="flagged-keterangan" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Flagged Status</button>
                            </form>
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
            // Initialize select2
            $('.select2').select2();

            const isSuperUser = @json(auth()->user()->can('Super User'));
            let masterData = {};

            let columns = [];

            if (isSuperUser) {
                columns.push({
                    data: null,
                    title: '',
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle',
                    width: '2%',
                    render: (_, __, row) =>
                        `<input type="checkbox" class="row-select" value="${row.id ?? ''}">`
                });
            }

            columns = columns.concat([{
                    data: 'company',
                    title: 'Perusahaan'
                },
                {
                    data: 'kompartemen',
                    title: 'Kompartemen'
                },
                {
                    data: 'departemen',
                    title: 'Departemen'
                },
                {
                    data: 'job_role_id',
                    title: 'Kode Job Role'
                },
                {
                    data: 'job_role',
                    title: 'Nama Jabatan'
                },
                {
                    data: 'deskripsi',
                    title: 'Deskripsi'
                },
                {
                    data: 'status',
                    title: 'Status',
                    render: function(data, type) {
                        // Use raw value for search/sort; decorate only for display
                        if (type !== 'display') return data;
                        if (data === 'Active') {
                            return '<span style="color:#fff;background:#28a745;padding:2px 8px;border-radius:4px;">Active</span>';
                        } else if (data === 'Not Active') {
                            return '<span style="color:#fff;background:#dc3545;padding:2px 8px;border-radius:4px;">Not Active</span>';
                        }
                        return data ?? '';
                    },
                    createdCell: function(td, cellData) {
                        if (cellData === 'Not Active') {
                            $(td).css({
                                'background': '#dc3545',
                                'color': '#fff'
                            });
                        } else if (cellData === 'Active') {
                            $(td).css({
                                'background': '#28a745',
                                'color': '#fff'
                            });
                        }
                    }
                },
                {
                    data: 'flagged',
                    title: 'Flagged',
                    render: function(data, type) {
                        // keep plain text so filtering works easily
                        if (type !== 'display') return data ? 'Yes' : 'No';
                        return data ? 'Yes' : 'No';
                    },
                    createdCell: function(td, cellData, rowData) {
                        if (rowData.flagged) {
                            if (!rowData.job_role_id || rowData.job_role_id === 'Not Assigned') {
                                $(td).css('background-color', '#f02e3f').css('color', '#fff');
                            } else {
                                $(td).css('background-color', '#fff3cd').css('color', '#000');
                            }
                        }
                    }
                },
                {
                    data: 'actions',
                    title: 'Actions',
                    width: '12.5%',
                    orderable: false,
                    searchable: false
                }
            ]);

            let jobRolesTable = $('#jobRolesTable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                data: [],
                columns: columns,
                initComplete: function() {
                    const api = this.api();
                    const $thead = $('#jobRolesTable thead');

                    // Build a filters row under the header
                    if ($thead.find('tr.filters').length === 0) {
                        const $filterRow = $('<tr class="filters"></tr>').appendTo($thead);

                        api.columns().every(function(colIdx) {
                            const column = this;
                            const title = $(column.header()).text().trim();
                            const $cell = $('<th></th>').appendTo($filterRow);

                            // Skip checkbox column (empty title) and Actions column
                            if (title === '' || title === 'Actions') return;

                            if (title === 'Status') {
                                const $select = $(`
                            <select class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="Active">Active</option>
                                <option value="Not Active">Not Active</option>
                            </select>
                        `).appendTo($cell);

                                $select.on('change', function() {
                                    const v = $(this).val();
                                    if (v) {
                                        // exact match with regex
                                        column.search('^' + $.fn.dataTable.util
                                                .escapeRegex(v) + '$', true, false)
                                            .draw();
                                    } else {
                                        column.search('').draw();
                                    }
                                });
                            } else if (title === 'Flagged') {
                                const $select = $(`
                            <select class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        `).appendTo($cell);

                                $select.on('change', function() {
                                    const v = $(this).val();
                                    column.search(v, false, false).draw();
                                });
                            } else {
                                $('<input type="text" class="form-control form-control-sm" placeholder="Search ' +
                                        title + '">')
                                    .appendTo($cell)
                                    .on('keyup change clear', function() {
                                        if (column.search() !== this.value) {
                                            column.search(this.value).draw();
                                        }
                                    });
                            }
                        });
                    }
                }
            });

            // Fetch master data
            $.ajax({
                url: '/storage/master_data.json',
                dataType: 'json',
                success: function(data) {
                    masterData = data;
                },
                error: function() {
                    alert('Failed to load master data.');
                }
            });

            // Handle company dropdown change
            $('#companyDropdown').on('change', function() {
                const companyId = $(this).val();

                resetDropdowns(['#kompartemenDropdown', '#departemenDropdown']);
                let companyData = masterData.find(c => c.company_id == companyId);

                if (companyData) {
                    // Populate kompartemen dropdown
                    populateDropdown('#kompartemenDropdown', companyData.kompartemen, 'kompartemen_id',
                        'nama');

                    // Populate departemen_without_kompartemen
                    populateDropdown('#departemenDropdown', companyData.departemen_without_kompartemen,
                        'departemen_id', 'nama');
                }

                loadJobRoles();
            });

            // Handle kompartemen dropdown change
            $('#kompartemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $(this).val();

                resetDropdowns(['#departemenDropdown']);
                let companyData = masterData.find(c => c.company_id == companyId);
                let kompartemenData = companyData?.kompartemen.find(k => k.kompartemen_id == kompartemenId);

                if (kompartemenData?.departemen.length) {
                    // Populate departemen dropdown based on selected kompartemen
                    populateDropdown('#departemenDropdown', kompartemenData.departemen, 'kompartemen_id',
                        'nama');
                }

                loadJobRoles();
            });

            // Handle departemen dropdown change
            $('#departemenDropdown').on('change', function() {
                loadJobRoles();
            });

            // Load job roles based on selected filters
            function loadJobRoles() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $('#kompartemenDropdown').val();
                console.log(kompartemenId);
                const departemenId = $('#departemenDropdown').val();

                $.ajax({
                    url: '/get-job-roles',
                    method: 'GET',
                    data: {
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    success: function(data) {
                        jobRolesTable.clear().rows.add(data).draw();
                    },
                    error: function() {
                        alert('Failed to fetch Job Roles.');
                    }
                });
            }

            // Helper function to populate dropdowns
            function populateDropdown(selector, items, valueField, textField) {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');
                if (items?.length) {
                    dropdown.prop('disabled', false);
                    items.sort((a, b) => a[textField].localeCompare(b[textField])).forEach(item => {
                        dropdown.append(`<option value="${item[valueField]}">${item[textField]}</option>`);
                    });
                } else {
                    dropdown.prop('disabled', true);
                }
            }

            // Helper function to reset dropdowns
            function resetDropdowns(selectors) {
                selectors.forEach(selector => {
                    $(selector).empty().append('<option value="">-- Select --</option>').prop('disabled',
                        true);
                });
            }

            /// Show Job Role Details in Modal
            $(document).on('click', '.show-job-role', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (!url) return;

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        $('#modal-job-role-details').html(response);
                        const modalEl = document.getElementById('showJobRoleModal');
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    },
                    error: function(xhr) {
                        $('#modal-job-role-details').html(
                            '<p class="text-danger">Unable to load job role details.</p>');
                        const modalEl = document.getElementById('showJobRoleModal');
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                });
            });

            $(document).on('click', '.flagged-job-role', function(e) {
                e.preventDefault();
                const jobRoleId = $(this).data('id');
                if (!jobRoleId) return;

                // Optionally, fetch flagged info via AJAX here if needed
                $('#flagged-job-role-id').val(jobRoleId);
                $('#flaggedJobRoleModal').modal('show');
                $('#flagged-job-role-actions').removeClass('d-none');
            });

            $('#flaggedJobRoleForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('job-roles.update-flagged-status') }}",
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#flaggedJobRoleModal').modal('hide');
                            loadJobRoles(); // Refresh table
                            alert(response.message);
                        } else {
                            alert(response.message || 'Failed to update flagged status.');
                        }
                    },
                    error: function(xhr) {
                        alert('Failed to update flagged status.');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });

            $('#downloadFlaggedBtn').on('click', function(e) {
                e.preventDefault();
                const company = $('#companyDropdown').val() || '';
                const url = new URL("{{ route('job-roles.export-flagged') }}", window.location.origin);
                if (company) url.searchParams.set('company_code', company);
                window.location.href = url.toString();
            });

            $('#export-job-userid-btn').on('click', function(e) {
                e.preventDefault();

                var params = [];
                var company = $('[name="company_id"], #company_id, #companyDropdown').val();
                var kompartemen = $('[name="kompartemen_id"], #kompartemen_id, #kompartemenDropdown').val();
                var departemen = $('[name="departemen_id"], #departemen_id, #departemenDropdown').val();
                var jobRole = $('[name="job_role_id"], #job_role_id, #jobRoleDropdown').val();
                var periode = $('[name="periode_id"], #periode_id, #periodeDropdown').val();

                if (company) params.push('company_id=' + encodeURIComponent(company));
                if (kompartemen) params.push('kompartemen_id=' + encodeURIComponent(kompartemen));
                if (departemen) params.push('departemen_id=' + encodeURIComponent(departemen));
                if (jobRole) params.push('job_role_id=' + encodeURIComponent(jobRole));
                if (periode) params.push('periode_id=' + encodeURIComponent(periode));

                var url = "{{ route('job-roles.export') }}";
                if (params.length) {
                    url += '?' + params.join('&');
                }
                window.open(url, '_blank');
            });

            // Bulk delete functionality
            if (isSuperUser) {
                $('#bulkDeleteButton').on('click', function() {
                    const selectedIds = $('.row-select:checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (!selectedIds.length) {
                        return alert('Tidak ada job role yang dipilih.');
                    }

                    if (!confirm('Anda yakin ingin menghapus job role terpilih?')) {
                        return;
                    }

                    $.ajax({
                        url: "{{ route('job-roles.bulk-delete') }}",
                        method: 'POST',
                        data: {
                            ids: selectedIds,
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            alert(response.message);
                            $('#bulkDeleteButton').addClass('d-none');
                            loadJobRoles();
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.message ||
                                'Gagal menghapus job role terpilih.');
                        }
                    });
                });

                $(document).on('change', '.row-select', function() {
                    const anyChecked = $('.row-select:checked').length > 0;
                    $('#bulkDeleteButton').toggleClass('d-none', !anyChecked);
                });
            }
        });
    </script>
@endsection
