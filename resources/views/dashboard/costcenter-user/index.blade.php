@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Dashboard User Generic</h1>

        {{-- <a href="{{ route('user-generic.create') }}" target="_blank" class="btn btn-outline-secondary mb-3"> --}}

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="periode_filter">Periode</label>
                <select id="periode_filter" class="form-select">
                    <option value="">All</option>
                    @foreach ($periodes as $p)
                        <option value="{{ $p->id }}">{{ $p->definisi }}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <table id="user_generic_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Perusahaan</th>
                    <th>User Code</th>
                    <th>Cost Code</th>
                    {{-- <th style="background-color:greenyellow">Nama</th>
                    <th style="background-color: greenyellow">Direktorat</th>
                    <th style="background-color: lightblue">Kompartemen</th>
                    <th style="background-color: greenyellow">Cost Center</th> --}}
                    <th style="background-color: greenyellow">Code Center</th>
                    <th style="background-color: greenyellow">Definisi</th>
                    <th style="background-color: lightblue">User Terdaftar</th>
                    {{-- <th style="background-color: lightblue">SK penunjukan</th> --}}
                    <th style="background-color:fuchsia">User Sebelumnya</th>
                    <th>Tipe Lisensi</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="userNIKModal" tabindex="-1" aria-labelledby="userNIKModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userNIKModalLabel">User NIK Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-user-nick-details">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#periode_filter').on('change', function() {
                userGenericTable.ajax.reload();
            });


            let userGenericTable = $('#user_generic_table').DataTable({
                processing: true,
                serverSide: false,
                // ajax: "{{ route('dashboard.user-generic') }}",
                ajax: {
                    url: "{{ route('dashboard.user-generic') }}",
                    data: function(d) {
                        d.periode_id = $('#periode_filter').val(); // send selected periode
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'group',
                        name: 'group'
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
                        data: 'cost_center',
                        name: 'cost_center'
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi'
                    },
                    {
                        data: 'current_user',
                        name: 'current_user'
                    },
                    // {
                    //     data: 'dokumen_keterangan',
                    //     name: 'dokumen_keterangan'
                    // },
                    {
                        data: 'prev_user',
                        name: 'prev_user'
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
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                columnDefs: [{
                    targets: [0],
                    visible: false
                }],
                order: [
                    [2, 'asc']
                ]
            });

        });
    </script>
@endsection
