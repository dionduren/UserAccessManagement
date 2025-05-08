$(document).ready(function () {
    // Run this on page load and on resize/orientation change
    function setViewportHeight() {
        // Multiply by 1% to get a value for a vh unit
        let vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    setViewportHeight();
    window.addEventListener('resize', setViewportHeight);
})

document.getElementById('sidebarToggle').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('show'); // Toggle 'show' class on mobile screens
    }
});

// Ensure sidebar visibility resets on larger screens
window.addEventListener('resize', function () {
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
$('.dropdown-toggle').on('click', function (e) {
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
$(".dropdown-toggle").each(function () {
    if ($(this).find(".active").length > 0) {
        $(this).slideDown();
    }
});

// Automatically expand dropdowns with active routes
$('.dropdown-content').each(function () {
    if ($(this).find('.active').length > 0) {
        $(this).slideDown();
        $(this).addClass('expanded').css('display', 'block'); // Ensure it's visible and expanded
    }
});