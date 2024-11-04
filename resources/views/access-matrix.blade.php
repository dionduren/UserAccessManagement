@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Access Matrix</h1>

        <!-- Notification Area -->
        <div id="notification" class="alert" aria-live="assertive" role="alert"></div>


        <!-- User-Role Matrix -->
        <h2>Assign Roles to Users</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover mt-4">
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
                                    <input type="checkbox" data-user="{{ $user->id }}" data-role="{{ $role->name }}"
                                        {{ $user->hasRole($role->name) ? 'checked' : '' }} onchange="toggleRole(this)">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Role-Permission Matrix -->
        <h2>Assign Permissions to Roles</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover mt-4">
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

    <script>
        // Function to display notification with success/failure feedback
        // function showNotification(message, type) {
        //     const notification = document.getElementById('notification');
        //     notification.className = `alert alert-${type} show`; // Add 'show' class to trigger fade-in
        //     notification.innerText = message;

        //     // Display the notification for 3 seconds, then fade out
        //     setTimeout(() => {
        //         notification.classList.remove('show'); // Remove 'show' class for fade-out
        //     }, 3000);
        // }

        function showNotification(message, type = 'success') {

            const notification = document.getElementById('notification');

            // Check if notification element exists
            if (!notification) {
                console.error("Notification element not found");
                return;
            }

            // Set alert type and show class
            notification.className = `alert alert-${type} show`; // Add Bootstrap alert class and show class
            notification.innerText = message;

            // console.log("Notification classname = ", notification.className);
            // console.log("Notification innerText = ", notification.innerText);

            // Display the notification for 3 seconds, then hide it
            setTimeout(() => {
                notification.classList.remove('show'); // Remove the show class to fade out
            }, 3000);
        }


        // Function to show loading indicators for AJAX requests
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

        // AJAX to toggle user role assignment
        function toggleRole(checkbox) {
            const userId = checkbox.getAttribute('data-user');
            const roleName = checkbox.getAttribute('data-role');
            const isChecked = checkbox.checked;

            toggleLoadingIndicator(checkbox, true); // Show loading indicator

            fetch("{{ route('access-matrix.assign-role') }}", {
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
                    // console.log("Raw response:", response); // Log the raw response object

                    // Check if the response is OK before parsing JSON
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    // Attempt to parse the response as JSON
                    return response.json();
                })
                .then(data => {
                    // console.log("Parsed JSON response:", data); // Log the parsed JSON response
                    showNotification(data.message, data.status == 'success' ? 'success' : data.status == 'warning' ?
                        'warning' : 'danger');
                })
                .catch(error => {
                    console.error("Error:", error); // Log any errors that occur
                    showNotification('An error occurred while updating the role.', 'danger');
                })
                .finally(() => {
                    toggleLoadingIndicator(checkbox, false); // Remove loading indicator
                });
        }

        // AJAX to toggle role permission assignment
        function togglePermission(checkbox) {
            const roleName = checkbox.getAttribute('data-role');
            const permissionName = checkbox.getAttribute('data-permission');
            const isChecked = checkbox.checked;

            toggleLoadingIndicator(checkbox, true); // Show loading indicator

            fetch("{{ route('access-matrix.assign-permission') }}", {
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
                    showNotification(data.message, data.status == 'success' ? 'success' : data.status == 'warning' ?
                        'warning' : 'danger');
                })
                .finally(() => {
                    toggleLoadingIndicator(checkbox, false); // Remove loading indicator
                });
        }
    </script>
@endsection
