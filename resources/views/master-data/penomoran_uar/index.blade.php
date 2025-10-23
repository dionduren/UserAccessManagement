@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <h1>Penomoran UAR List</h1>
        <a href="{{ route('penomoran-uar.create') }}" class="btn btn-primary mb-5">Buat MD Penomoran UAR Baru</a>

        <table id="penomoran-uar-table" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th width="10%">Perusahaan</th>
                    <th width="10%">Unit Kerja ID</th>
                    <th width="10%">Level Unit Kerja</th>
                    <th>Unit Kerja</th>
                    <th width="10%">Nomor</th>
                    <th width="10%">Actions</th>
                </tr>
                <tr class="filters">
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Perusahaan"
                            data-col="0"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Unit Kerja ID"
                            data-col="1"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Level" data-col="2">
                    </th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Unit Kerja"
                            data-col="3"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Nomor" data-col="4">
                    </th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            const table = $('#penomoran-uar-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '{{ route('penomoran-uar.index') }}',
                columns: [{
                        data: 'company_id',
                        name: 'company_id'
                    }, {
                        data: 'unit_kerja_id',
                        name: 'unit_kerja_id'
                    },
                    {
                        data: 'level_unit_kerja',
                        name: 'level_unit_kerja'
                    },
                    {
                        data: 'unit_kerja',
                        name: 'unit_kerja'
                    },
                    {
                        data: 'number',
                        name: 'number'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
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
                order: [
                    [3, 'asc']
                ],
                initComplete: function() {
                    // Wire column filters
                    $('#penomoran-uar-table thead tr.filters input').on('keyup change', function() {
                        const colIdx = $(this).data('col');
                        const val = this.value;
                        if (table.column(colIdx).search() !== val) {
                            table.column(colIdx).search(val).draw();
                        }
                    });
                }
            });
        });
    </script>
@endsection
