@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Cost Center - Previous User</h1>

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

        <table id="prev_user_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Cost Code</th>
                    <th>User Code</th>
                    <th>Nama</th>
                    <th>Perlu Diperhatikan</th>
                    <th>Keterangan</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>

        <!-- Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Flagged and Keterangan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm" action="{{ route('prev-user.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="id" id="id">

                            <div class="form-group">
                                <label for="flagged">Perlu Diperhatikan</label>
                                <select name="flagged" id="flagged" class="form-control">
                                    <option value="false">No</option>
                                    <option value="true">Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" form="editForm" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let masterData = {}; // Store parsed JSON for efficient lookups

            let userGenericTable = $('#prev_user_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('prev-user.index') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'cost_code',
                        name: 'cost_code'
                    },
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'flagged',
                        name: 'Perlu Diperhatikan',
                        render: function(data, type, row, meta) {
                            return `<div style="text-align: center">${data === true ? '<i class="bi bi-exclamation-triangle-fill" style="color: red"></i>' : '-' }</div>`;
                        }
                    },
                    {
                        data: 'keterangan',
                        name: 'Keterangan'
                    },
                    {
                        data: 'action',
                        name: 'action',
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
                columnDefs: [{
                    targets: [0],
                    visible: false
                }],
                order: [
                    [0, 'asc']
                ],
                // drawCallback: function(settings) {
                //     // Add event listener to edit button
                //     $('#editModal').on('show.bs.modal', function(event) {
                //         let button = $(event.relatedTarget);
                //         let id = button.data('id');
                //         let flagged = button.data('flagged');
                //         let keterangan = button.data('keterangan');

                //         $('#editForm #id').val(id);
                //         $('#editForm #flagged').val(flagged);
                //         $('#editForm #keterangan').val(keterangan);
                //     });
                // }
            });

            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const flagged = $(this).data('flagged');
                const keterangan = $(this).data('keterangan');

                $('#editForm #id').val(id);
                $('#editForm #flagged').val(flagged);
                $('#editForm #keterangan').val(keterangan);

                $('#editModal').modal('show');
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);

                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#editModal').modal('hide');
                            $('#prev_user_table').DataTable().ajax.reload(null,
                                false); // refresh without pagination reset
                        }
                    }
                });
            });



        });
    </script>
@endsection
