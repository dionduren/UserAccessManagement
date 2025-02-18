<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'User Access Management') }}</title>

    <!-- Web App Icon -->
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="User Access Management" />
    <link rel="manifest" href="/site.webmanifest" />


    <!-- jQuery (required for Select2 and DataTables) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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
            width: 300px;
            min-height: 100vh;
            height: calc(var(--vh, 1vh) * 100);
            background-color: #343a40;
            /* Background color for visibility */
            color: white;
            transition: transform 0.3s ease;
        }

        /* Hide sidebar toggle button by default */
        #sidebarToggle {
            display: none;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            /* justify-content: space-between; */
            color: white !important;
            /* Ensures content is spaced properly */
        }

        .dropdown-toggle::after {
            font-family: "Bootstrap Icons";
            font-weight: bold;
            float: right;
            rotate: -90deg;
            transition: transform 0.3s ease-in-out;
        }

        /* Rotate arrow when dropdown is open */
        .dropdown-toggle.active::after {
            -webkit-transform: rotate(90deg);
            -moz-transform: rotate(90deg);
            -ms-transform: rotate(90deg);
            -o-transform: rotate(90deg);
            transform: rotate(90deg);
        }

        /* Dropdown content */
        .dropdown-content {
            list-style: none;
            margin: 0;
            padding: 0;
            display: none;
            /* Hidden by default */
        }

        /* Expanded state for dropdown content */
        .dropdown-content.expanded {
            display: block;
            /* Ensure it's visible when expanded */
        }

        /* Individual dropdown items */
        .dropdown-content li {
            padding-left: 20px;
            /* Indentation for child items */
        }

        .nav-link.active {
            background: rgb(241 245 249) !important;
            color: black !important;
            border-radius: 0;
            border-top-left-radius: 80px 80px;
            border-bottom-left-radius: 80px 80px;
            font-weight: bold;
        }

        .nav-link {
            transition: all 0.3s ease;
            padding-right: 10px;
        }

        .nav-link:hover,
        .nav-link.active:hover {
            background-color: rgba(0, 132, 255, 0.96) !important;
            /* Optional hover effect */
            color: white !important;
            border-radius: 0;
            border-top-left-radius: 80px 80px;
            border-bottom-left-radius: 80px 80px;
            /* Ensure text is visible on hover */
        }

        .content-padding {
            padding-top: 1em;
            padding-inline-start: 0;
            padding-bottom: 0;
            min-height: 100vh;
        }

        .content-card {
            background: rgb(241 245 249);
            border-radius: 30px;
            min-height: 92vh;
        }

        /* NEW CSS TESTING */
        .top-bar {
            background: rgb(241 245 249);
            color: black;
            padding: 10px 0 0 20px;
            margin-bottom: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-radius: 25px;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 0;
            color: black;
        }

        /* Notification & Profile Dropdowns */
        .dropdown-menu {
            width: 250px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu .dropdown-item {
            padding: 10px;
        }

        /* Notification UI */
        .notification-card {
            padding: 10px;
        }

        .divider {
            height: 1px;
            background: #ccc;
            margin: 5px 0;
        }

        /* Profile */
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        /* NEW CSS TESTING */

        /* Sidebar and Toggle Button Behavior on Mobile */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                transform: translateX(-100%);
                /* Initially hidden on mobile */
                z-index: 1000;
                height: calc(var(--vh, 1vh) * 100);
                /* height: 100vh; */
                /* Explicit height helps inner 100% height calculations */
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

            .content-padding {
                padding: 1em;
                min-height: 100vh;
            }

            .content-card {
                background-color: white;
                border-radius: 15px;
                min-height: 92vh;
                padding-top: 0.75em;
            }
        }
    </style>
</head>

<body>
    <div id="backdrop px-4" style="background-color: #343a40">

    </div>
    <div id="app" style="background-color: #343a40; padding: 10;">
        <!-- Sidebar Toggle Button for Mobile -->
        <button class="btn btn-primary d-md-none" id="sidebarToggle">
            <i class="bi bi-list"></i> Menu
        </button>

        <!-- Sidebar -->
        <div id="sidebar" class="d-flex flex-column flex-shrink-0"
            style="padding-inline-start: 30px; padding-top: 30px">
            @include('layouts.sidebar')
        </div>


        <!-- Main Content Area -->
        {{-- <div class="flex-grow-1 px-0 py-4" style="border-radius: 25px; background-color: white"> --}}
        <div class="flex-grow-1 content-padding no-scrollbar">
            {{-- Content Area --}}
            <main class="container-fluid content-card pt-1">

                <!-- Top Bar -->
                @include('layouts.top-bar')

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Run this on page load and on resize/orientation change
            function setViewportHeight() {
                // Multiply by 1% to get a value for a vh unit
                let vh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--vh', `${vh}px`);
            }

            setViewportHeight();
            window.addEventListener('resize', setViewportHeight);
        })

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

        // Toggle dropdowns on click
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();

            const content = $(this).next(".dropdown-content");

            // Close any other open dropdowns
            $(".dropdown-content").not(content).slideUp();
            $(".dropdown-toggle").not(this).removeClass("active");

            // Toggle the current dropdown and arrow
            content.slideToggle();
            $(this).toggleClass("active");
        });

        // Ensure the dropdown is open if any child route is active
        $(".dropdown-toggle").each(function() {
            if ($(this).find(".active").length > 0) {
                $(this).slideDown();
            }
        });

        // Automatically expand dropdowns with active routes
        $('.dropdown-content').each(function() {
            if ($(this).find('.active').length > 0) {
                $(this).slideDown();
                $(this).addClass('expanded').css('display', 'block'); // Ensure it's visible and expanded
            }
        });
    </script>

    @yield('scripts')

</body>

</html>
