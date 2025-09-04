$(document).ready(function () {
    // Initialize DataTables for the local data not found in Middle DB
    $('#localDataTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/api/local-data', // Adjust the URL to your API endpoint
            type: 'GET'
        },
        columns: [
            { data: 'index', name: 'index' },
            { data: 'level', name: 'level' },
            { data: 'value', name: 'value' }
        ],
        searching: true,
        paging: true,
        order: [[0, 'asc']]
    });

    // Initialize DataTables for the Middle DB data not found locally
    $('#middleDbDataTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/api/middle-db-data', // Adjust the URL to your API endpoint
            type: 'GET'
        },
        columns: [
            { data: 'index', name: 'index' },
            { data: 'level', name: 'level' },
            { data: 'value', name: 'value' }
        ],
        searching: true,
        paging: true,
        order: [[0, 'asc']]
    });

    // Updated to match actual table IDs and avoid errors if file is included globally.
    $(function () {

        function initIfExists(id) {
            const sel = '#' + id;
            if (!$(sel).length) return;
            if ($.fn.dataTable.isDataTable(sel)) return;

            const dt = $(sel).DataTable({
                paging: true,
                ordering: true,
                info: true,
                responsive: true,
                lengthChange: true,
                pageLength: 25,
                order: [[1, 'asc'], [2, 'asc']],
                columnDefs: [{ targets: 0, orderable: false }]
            });

            $(sel + ' thead tr.dt-filters th').each(function (i) {
                const input = $('input', this);
                if (input.length) {
                    input.on('keyup change', function () {
                        if (dt.column(i).search() !== this.value) {
                            dt.column(i).search(this.value).draw();
                        }
                    });
                }
            });

            dt.on('draw.dt order.dt search.dt page.dt', function () {
                let start = dt.page.info().start;
                dt.column(0, { page: 'current' }).nodes().each(function (cell, idx) {
                    cell.innerHTML = start + idx + 1;
                });
            });
        }

        initIfExists('local-missing-table');
        initIfExists('middle-missing-table');
    });
});