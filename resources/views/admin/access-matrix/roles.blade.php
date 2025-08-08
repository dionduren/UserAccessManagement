@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Assign Roles to Users</h4>
            </div>
            <div class="card-body">
                <div id="am-notif" class="alert d-none"></div>
                <div class="table-responsive">
                    <table id="am-roles" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                @foreach ($roles as $r)
                                    <th>{{ $r->name }}</th>
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
                {
                    data: 'email',
                    name: 'email'
                },
                @foreach ($roles as $r)
                    {
                        data: 'role_{{ \Illuminate\Support\Str::slug($r->name, '_') }}',
                        name: 'role_{{ \Illuminate\Support\Str::slug($r->name, '_') }}',
                        orderable: false,
                        searchable: false
                    },
                @endforeach
            ];

            const table = $('#am-roles').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.access-matrix.roles.data') }}',
                columns,
                order: [
                    [0, 'asc']
                ],
                drawCallback: function() {
                    $('.role-toggle').off('change').on('change', function() {
                        const el = this;
                        el.disabled = true;
                        fetch('{{ route('admin.access-matrix.assign-role') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    user_id: el.dataset.user,
                                    role_name: el.dataset.role,
                                    assign: el.checked
                                })
                            }).then(r => r.json()).then(j => {
                                showNotif(j.message, j.status === 'success' ? 'success' : (j
                                    .status === 'warning' ? 'warning' : 'danger'));
                            }).catch(() => showNotif('Failed to update role', 'danger'))
                            .finally(() => el.disabled = false);
                    });
                }
            });

            function showNotif(msg, type) {
                const box = document.getElementById('am-notif');
                box.className = 'alert alert-' + type;
                box.textContent = msg;
                box.classList.remove('d-none');
                setTimeout(() => box.classList.add('d-none'), 2500);
            }
        });
    </script>
@endsection
