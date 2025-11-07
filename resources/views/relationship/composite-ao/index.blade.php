@extends('layouts.app')

@section('header-scripts')
    <style>
        table.table-sm td,
        table.table-sm th {
            padding: .45rem .5rem;
            vertical-align: top !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap align-items-center gap-3">
                <h4 class="mb-0">Composite AO Mapping</h4>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger py-2">{{ session('error') }}</div>
                @endif
                <div class="ms-auto">
                    <a href="{{ route('composite_ao.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Create Authorization Object
                    </a>
                </div>
                @if ($userCompanyCode === 'A000')
                    <div>
                        <label class="form-label mb-1 small">Company</label>
                        <select id="companyFilter" class="form-select form-select-sm">
                            <option value="">-- All --</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->company_code }}" @selected($selectedCompany === $c->company_code)>
                                    {{ $c->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <span class="badge bg-secondary">
                        Company: {{ $companies->first()->nama }} ({{ $companies->first()->company_code }})
                    </span>
                @endif
                <div class="table-responsive">
                    <table id="aoTable" class="table table-sm table-bordered w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">Company</th>
                                <th width="25%">Composite Role</th>
                                <th width="25%">Authorization Object</th>
                                <th>Deskripsi</th>
                                <th width="12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#aoTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('composite_ao.datatable') }}',
                    data: function(d) {
                        d.company_id = $('#companyFilter').val();
                    }
                },
                lengthMenu: [10, 25, 50, 100],
                columns: [{
                        data: 'company',
                        name: 'company'
                    },
                    {
                        data: 'composite_role',
                        name: 'composite_role'
                    },
                    {
                        data: 'ao_name',
                        name: 'ao_name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#companyFilter').on('change', () => table.ajax.reload());

            $('#aoTable').on('click', '.btn-delete', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Delete?',
                    text: 'Remove this Composite AO entry?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete'
                }).then(res => {
                    if (!res.isConfirmed) return;
                    fetch("{{ url('composite-ao') }}/" + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (j.status === 'ok') {
                                Swal.fire('Deleted', 'Entry removed', 'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', 'Failed to delete', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Failed to delete', 'error'));
                });
            });
        });
    </script>
@endsection
