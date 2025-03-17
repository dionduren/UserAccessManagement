@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Compare User NIK Data Between Periodes</h1>

        <div class="form-group">
            <label for="periode1">Select First Periode</label>
            <select name="periode1" id="periode1" class="form-control">
                <option value="">Select First Periode</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="periode2">Select Second Periode</label>
            <select name="periode2" id="periode2" class="form-control">
                <option value="">Select Second Periode</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <table id="user_nik_table1" class="table table-bordered table-striped table-hover mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Cost Code</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Dokumen Keterangan</th>
                </tr>
            </thead>
        </table>

        <table id="user_nik_table2" class="table table-bordered table-striped table-hover mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Cost Code</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Dokumen Keterangan</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            function loadTable(tableId, periodeId) {
                $('#' + tableId).DataTable({
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    ajax: {
                        url: "{{ route('user-nik.get-periodic') }}",
                        data: {
                            periode: periodeId
                        }
                    },
                    columns: [{
                            data: 'id',
                            name: 'id'
                        },
                        {
                            data: 'cost_code',
                            name: 'cost_code'
                        },
                        {
                            data: 'nama',
                            name: 'nama'
                        },
                        {
                            data: 'nik',
                            name: 'nik'
                        },
                        {
                            data: 'dokumen_keterangan',
                            name: 'dokumen_keterangan'
                        }
                    ],
                    responsive: true
                });
            }

            $('#periode1').on('change', function() {
                let periodeId = $(this).val();
                if (periodeId) {
                    loadTable('user_nik_table1', periodeId);
                }
            });

            $('#periode2').on('change', function() {
                let periodeId = $(this).val();
                if (periodeId) {
                    loadTable('user_nik_table2', periodeId);
                }
            });
        });
    </script>
@endsection
