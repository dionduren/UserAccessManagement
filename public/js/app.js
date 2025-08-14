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

// Toggle dropdowns on click (sync rotation via aria-expanded)
$('.dropdown-toggle').on('click', function (e) {
    e.preventDefault();

    const $trigger = $(this);
    const $content = $trigger.next('.dropdown-content');
    const isOpen = $trigger.attr('aria-expanded') === 'true';

    // Close other dropdowns
    $('.dropdown-content').not($content).slideUp(150).removeClass('expanded');
    $('.dropdown-toggle').not($trigger).attr('aria-expanded', 'false').removeClass('active');

    // Toggle current
    if (isOpen) {
        $content.slideUp(150).removeClass('expanded');
        $trigger.attr('aria-expanded', 'false').removeClass('active');
    } else {
        $content.slideDown(150).addClass('expanded').css('display', 'block');
        $trigger.attr('aria-expanded', 'true').addClass('active');
    }
});

// Remove the earlier .each() that tried to slideDown() the toggle itself.
// Ensure initial state matches server-rendered routes (.show) and rotate caret
$('.dropdown-content').each(function () {
    const $panel = $(this);
    const $trigger = $panel.prev('.dropdown-toggle');

    if ($panel.hasClass('show') || $panel.find('.active').length > 0) {
        $panel.show().addClass('expanded');
        $trigger.attr('aria-expanded', 'true').addClass('active');
    } else {
        $panel.hide().removeClass('expanded');
        $trigger.attr('aria-expanded', 'false').removeClass('active');
    }
});