@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Preview User NIK</h1>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <!-- Datatable -->
        <table id="userNIKTable" class="table table-bordered display responsive nowrap">
        </table>

        <!-- Progress Bar -->
        <div id="progress-container" class="my-4" style="display: none;">
            <h5>Uploading Data...</h5>
            <div class="progress">
                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                    aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>

        <!-- Form for Confirmation -->
        <form id="confirm-form" action="{{ route('user-nik.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Import</button>
            <a href="{{ route('user-nik.upload.form') }}" class="btn btn-secondary">Cancel</a>
        </form>

        <!-- Redirect Buttons after Success -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('user-nik.upload.form') }}" class="btn btn-primary">Back to Upload Page</a>
                <a href="{{ route('home') }}" class="btn btn-secondary">Go to Home Page</a>
            </div>
        @endif

        <!-- Error Upload Info -->
        <div id="error-upload-info" style="display: none;">
            <div class="alert alert-danger">
                <span id="error-message"></span>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#userNIKTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('user-nik.upload.preview_data') }}",
                columns: [{
                        data: 'group',
                        title: 'Perusahaan',
                        render: editableField('group')
                    },
                    {
                        data: 'user_code',
                        title: 'NIK',
                        render: editableField('user_code')
                    },
                    {
                        data: 'user_type',
                        title: 'User Type',
                        render: editableField('user_type')
                    },
                    {
                        data: 'license_type',
                        title: 'License Type',
                        render: editableField('license_type')
                    },
                    {
                        data: 'last_login',
                        title: 'Login Terakhir',
                        render: editableField('last_login')
                    },
                    {
                        data: 'valid_from',
                        title: 'Valid From',
                        render: editableField('valid_from')
                    },
                    {
                        data: 'valid_to',
                        title: 'Valid To',
                        render: editableField('valid_to')
                    },
                ],
                responsive: true,
                searching: true,
                paging: true,
            });

            function editableField(column) {
                return function(data, type, row, meta) {
                    return `<input type="text" class="form-control form-control-sm inline-edit" value="${data}" data-row-id="${row.id}" data-column="${column}">`;
                };
            }

            $('#userNIKTable').on('change', 'input.inline-edit', function() {
                const input = $(this);
                const row = $('#userNIKTable').DataTable().row(input.closest('tr')).data();
                const rowIndex = row._row_index; // explicitly using original index
                const column = input.data('column');
                const value = input.val();

                fetch('{{ route('user-nik.upload.update-inline-session') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({
                            row_index: rowIndex,
                            column: column,
                            value: value
                        })
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            console.log('Session data updated successfully.');
                        } else {
                            console.error('Failed to update:', response.error);
                        }
                    })
                    .catch(err => console.error('Error:', err));
            });

            const confirmForm = document.getElementById('confirm-form');
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');

            confirmForm.addEventListener('submit', function(e) {
                e.preventDefault();
                progressContainer.style.display = 'block';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', confirmForm.action);
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));

                xhr.onprogress = function(e) {
                    const lines = e.currentTarget.responseText.trim().split('\n');
                    const lastLine = lines[lines.length - 1];

                    try {
                        const data = JSON.parse(lastLine);
                        if (data.progress !== undefined) {
                            progressBar.style.width = data.progress + '%';
                            progressBar.textContent = data.progress.toFixed(0) + '%';
                        }

                        if (data.progress >= 100) {
                            progressBar.textContent = '100%';
                            location.href = "{{ route('user-nik.index') }}";
                        }
                    } catch (err) {
                        console.error('Parsing error:', err);
                    }
                };

                xhr.onerror = function() {
                    alert('An error occurred.');
                    progressContainer.style.display = 'none';
                };

                xhr.send(new FormData(confirmForm));
            });
        });
    </script>
@endsection
