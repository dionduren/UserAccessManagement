@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Compare User Generic Data Between Periodes</h1>

        <div class="form-group">
            <label for="periode1">Select First Periode</label>
            <select name="periode1" id="periode1" class="form-control">
                <option value="">Select First Periode</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <table id="user_generic_table1" class="table table-bordered table-striped table-hover mt-3">
            <thead>
                <tr>
                    <th>Perusahaan</th>
                    <th>Periode</th>
                    <th>User Code</th>
                    <th>Cost Code</th>
                    <th>Tipe Lisensi</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                </tr>
            </thead>
        </table>

        <hr style="border-width: 3px;" class="my-4">

        <div class="form-group">
            <label for="periode2">Select Second Periode</label>
            <select name="periode2" id="periode2" class="form-control">
                <option value="">Select Second Periode</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <table id="user_generic_table2" class="table table-bordered table-striped table-hover mt-3">
            <thead>
                <tr>
                    <th>Perusahaan</th>
                    <th>Periode</th>
                    <th>User Code</th>
                    <th>Cost Code</th>
                    <th>Tipe Lisensi</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
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
                    serverSide: false,
                    destroy: true,
                    ajax: {
                        url: "{{ route('user-generic.get-periodic') }}",
                        data: {
                            periode: periodeId
                        }
                    },
                    columns: [{
                            data: 'group',
                            name: 'group'
                        },
                        {
                            data: 'periode',
                            name: 'periode'
                        },
                        {
                            data: 'user_code',
                            name: 'user_code'
                        },
                        {
                            data: 'cost_code',
                            name: 'cost_code'
                        },
                        {
                            data: 'license_type',
                            name: 'license_type'
                        },
                        {
                            data: 'valid_from',
                            name: 'valid_from'
                        },
                        {
                            data: 'valid_to',
                            name: 'valid_to'
                        }
                    ],
                    responsive: true
                });
            }

            $('#periode1').on('change', function() {
                let periodeId = $(this).val();
                if (periodeId) {
                    loadTable('user_generic_table1', periodeId);
                }
            });

            $('#periode2').on('change', function() {
                let periodeId = $(this).val();
                if (periodeId) {
                    loadTable('user_generic_table2', periodeId);
                }
            });
        });
    </script>
@endsection
