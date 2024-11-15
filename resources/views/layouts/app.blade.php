<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'User App Management') }}</title>

    <!-- jQuery (required for Select2 and DataTables) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">


    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


    <style>
        /* Sidebar and Content Container */
        #app {
            display: flex;
            flex-direction: row;
            height: 100vh;
            /* Ensure full viewport height */
        }

        /* Main Content Area */
        .flex-grow-1 {
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto;
            /* Ensure the main content can scroll */
        }

        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 300px;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        #notification.show {
            opacity: 1;
        }

        /* Custom styling for Access Matrix responsiveness */
        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 0.9rem;
        }

        /* Sidebar styling */
        #sidebar {
            width: 280px;
            min-height: 100vh;
            background-color: #343a40;
            /* Background color for visibility */
            color: white;
            transition: transform 0.3s ease;
        }

        /* Hide sidebar toggle button by default */
        #sidebarToggle {
            display: none;
        }

        /* Sidebar and Toggle Button Behavior on Mobile */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                transform: translateX(-100%);
                /* Initially hidden on mobile */
                z-index: 1000;
            }

            #sidebar.show {
                transform: translateX(0);
                /* Slide in when the "show" class is added */
            }

            /* Display the toggle button on mobile screens */
            #sidebarToggle {
                display: inline-block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 1100;
            }
        }
    </style>
</head>

<body>
    <div id="app" class="d-flex">
        <!-- Sidebar Toggle Button for Mobile -->
        <button class="btn btn-primary d-md-none" id="sidebarToggle">
            <i class="bi bi-list"></i> Menu
        </button>

        <!-- Sidebar -->
        <div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white"
            style="width: 280px; min-height: 100vh;">
            @include('layouts.sidebar')
        </div>

        <!-- Main Content Area -->
        <div class="flex-grow-1 p-4">
            <main class="container-fluid">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');

            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show'); // Toggle 'show' class on mobile screens
            }
        });

        // Ensure sidebar visibility resets on larger screens
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');

            if (window.innerWidth > 768) {
                sidebar.classList.remove('show'); // Ensure the sidebar is visible on desktop
            }
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @yield('scripts')

</body>

</html>
