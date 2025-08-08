@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Assign Permissions to Roles</h4>
            </div>
            <div class="card-body">
                <div id="ap-notif" class="alert d-none"></div>
                <div class="table-responsive">
                    <table id="am-permissions" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Role</th>
                                @foreach ($permissions as $p)
                                    <th>{{ $p->name }}</th>
                                @endforeach
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const columns = [{
                    data: 'name',
                    name: 'name'
                },
                @foreach ($permissions as $p)
                    {
                        data: 'perm_{{ \Illuminate\Support\Str::slug($p->name, '_') }}',
                        name: 'perm_{{ \Illuminate\Support\Str::slug($p->name, '_') }}',
                        orderable: false,
                        searchable: false
                    },
                @endforeach
            ];

            const table = $('#am-permissions').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.access-matrix.permissions.data') }}',
                columns,
                order: [
                    [0, 'asc']
                ],
                drawCallback: function() {
                    $('.perm-toggle').off('change').on('change', function() {
                        const el = this;
                        el.disabled = true;
                        fetch('{{ route('admin.access-matrix.assign-permission') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    role_name: el.dataset.role,
                                    permission_name: el.dataset.permission,
                                    assign: el.checked
                                })
                            }).then(r => r.json()).then(j => {
                                showNotif(j.message, j.status === 'success' ? 'success' : (j
                                    .status === 'warning' ? 'warning' : 'danger'));
                            }).catch(() => showNotif('Failed to update permission', 'danger'))
                            .finally(() => el.disabled = false);
                    });
                }
            });

            function showNotif(msg, type) {
                const box = document.getElementById('ap-notif');
                box.className = 'alert alert-' + type;
                box.textContent = msg;
                box.classList.remove('d-none');
                setTimeout(() => box.classList.add('d-none'), 2500);
            }
        });
    </script>
@endsection
