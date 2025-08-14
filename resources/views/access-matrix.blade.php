@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">Access Matrix</h1>
                    </div>
                    <div class="card-body">
                        <!-- Notification Area -->
                        <div id="notification" class="alert mb-3" aria-live="assertive" role="alert"></div>

                        <!-- User-Role Matrix -->
                        <h2 class="h5">Assign Roles to Users</h2>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mt-3">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        @foreach ($roles as $role)
                                            <th>{{ $role->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            @foreach ($roles as $role)
                                                <td>
                                                    <input type="checkbox" data-user="{{ $user->id }}"
                                                        data-role="{{ $role->name }}"
                                                        {{ $user->hasRole($role->name) ? 'checked' : '' }}
                                                        onchange="toggleRole(this)">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <!-- Role-Permission Matrix -->
                        <h2 class="h5">Assign Permissions to Roles</h2>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mt-3">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        @foreach ($permissions as $permission)
                                            <th>{{ $permission->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        <tr>
                                            <td>{{ $role->name }}</td>
                                            @foreach ($permissions as $permission)
                                                <td>
                                                    <input type="checkbox" data-role="{{ $role->name }}"
                                                        data-permission="{{ $permission->name }}"
                                                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                        onchange="togglePermission(this)">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            if (!notification) {
                console.error("Notification element not found");
                return;
            }
            notification.className = `alert alert-${type} show`;
            notification.innerText = message;
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        function toggleLoadingIndicator(element, isLoading) {
            if (isLoading) {
                element.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                element.disabled = true;
            } else {
                element.innerHTML = '';
                element.disabled = false;
            }
        }

        function toggleRole(checkbox) {
            const userId = checkbox.getAttribute('data-user');
            const roleName = checkbox.getAttribute('data-role');
            const isChecked = checkbox.checked;

            toggleLoadingIndicator(checkbox, true);

            fetch("{{ route('admin.access-matrix.assign-role') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        role_name: roleName,
                        assign: isChecked
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    showNotification(
                        data.message,
                        data.status == 'success' ? 'success' : data.status == 'warning' ? 'warning' : 'danger'
                    );
                })
                .catch(error => {
                    console.error("Error:", error);
                    showNotification('An error occurred while updating the role.', 'danger');
                })
                .finally(() => {
                    toggleLoadingIndicator(checkbox, false);
                });
        }

        function togglePermission(checkbox) {
            const roleName = checkbox.getAttribute('data-role');
            const permissionName = checkbox.getAttribute('data-permission');
            const isChecked = checkbox.checked;

            toggleLoadingIndicator(checkbox, true);

            fetch("{{ route('admin.access-matrix.assign-permission') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        role_name: roleName,
                        permission_name: permissionName,
                        assign: isChecked
                    })
                })
                .then(response => response.json())
                .then(data => {
                    showNotification(data.message, data.status === 'success' ? 'success' : 'danger');
                })
                .catch(error => {
                    showNotification('An error occurred while updating the permission.', 'danger');
                })
                .finally(() => {
                    toggleLoadingIndicator(checkbox, false);
                });
        }
    </script>
@endsection
