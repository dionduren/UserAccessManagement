@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>User Generic Tanpa Relasi Job Role</h1>

        <div class="form-group">
            <label for="periode">Periode</label>
            <select name="periode" id="periode" class="form-control">
                <option value="">Silahkan Pilih Periode Data</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <table id="user_generic_no_jobrole_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Perusahaan</th>
                    <th>Kompartemen</th>
                    <th>Departemen</th>
                    <th>User Generic</th>
                    <th>Last Login</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#user_generic_no_jobrole_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: false, // Don't load data initially
                columns: [{
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'group',
                        name: 'group'
                    },
                    {
                        data: 'kompartemen',
                        name: 'kompartemen'
                    },
                    {
                        data: 'departemen',
                        name: 'departemen'
                    },
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'last_login',
                        name: 'last_login',
                        render: function(data, type, row) {
                            if (!data) return '';
                            var date = new Date(data);
                            if (type === 'sort' || type === 'type') {
                                // Return sortable format: YYYYMMDD
                                var y = date.getFullYear();
                                var m = String(date.getMonth() + 1).padStart(2, '0');
                                var d = String(date.getDate()).padStart(2, '0');
                                return y + m + d;
                            }
                            // Display format: DD Month YYYY
                            var day = String(date.getDate()).padStart(2, '0');
                            var month = date.toLocaleString('id-ID', {
                                month: 'long'
                            });
                            var year = date.getFullYear();
                            return day + ' ' + month + ' ' + year;
                        }
                    }
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });

            $('#periode').on('change', function() {
                var periode = $(this).val();
                if (periode) {
                    table.ajax.url("{{ route('user-generic-job-role.null-relationship') }}?periode=" +
                        periode).load();
                } else {
                    table.clear().draw();
                }
            });
        });
    </script>
@endsection
