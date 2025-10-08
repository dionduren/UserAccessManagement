@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Dashboard User System</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="form-group">
            <label for="periode">Periode</label>
            <select name="periode" id="periode" class="form-control">
                <option value="">Silahkan Pilih Periode Data</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <table id="user_system_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th style="width:8%;">Perusahaan</th>
                    <th style="width:10%;">User Code</th>
                    <th style="width:15%;">User Profile</th>
                    <th style="width:8%;">User Type</th>
                    <th style="width:8%;">License</th>
                    <th style="width:10%;">Cost Code</th>
                    <th style="width:8%;">Valid From</th>
                    <th style="width:8%;">Valid To</th>
                    <th style="width:10%;">Last Login</th>
                    <th style="width:6%;">Flagged</th>
                    <th style="width:12%;">Action</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#user_system_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('user-system.index') }}",
                    data: function(d) {
                        d.periode = $('#periode').val();
                    },
                    dataSrc: function(json) {
                        return json.data || [];
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'user_profile',
                        name: 'user_profile'
                    },
                    {
                        data: 'user_type',
                        name: 'user_type'
                    },
                    {
                        data: 'license_type',
                        name: 'license_type'
                    },
                    {
                        data: 'cost_code',
                        name: 'cost_code'
                    },
                    {
                        data: 'valid_from',
                        name: 'valid_from'
                    },
                    {
                        data: 'valid_to',
                        name: 'valid_to'
                    },
                    {
                        data: 'last_login',
                        name: 'last_login'
                    },
                    {
                        data: 'flagged',
                        name: 'flagged',
                        render: function(d) {
                            return d ? '<span class="badge bg-danger">Flagged</span>' : '';
                        },
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                responsive: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                columnDefs: [{
                    targets: [0],
                    visible: false
                }]
            });

            $('#periode').on('change', function() {
                if ($(this).val()) {
                    table.ajax.reload();
                } else {
                    table.clear().draw();
                }
            });
        });

        function deleteUserSystem(id) {
            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Data akan dihapus permanen.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus",
                cancelButtonText: "Batal"
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/user-system/' + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(resp) {
                            Swal.fire("Deleted!", resp.message || 'Deleted.', "success");
                            $('#user_system_table').DataTable().ajax.reload(null, false);
                        },
                        error: function() {
                            Swal.fire("Error", "Gagal menghapus.", "error");
                        }
                    });
                }
            });
        }
    </script>
@endsection
