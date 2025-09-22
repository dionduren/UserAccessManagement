@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">Upload Single Role Tcode</h1>
                    </div>
                    <div class="card-body">

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session('validationErrors'))
                            <div class="alert alert-danger">
                                <h4>Validation Errors:</h4>
                                <ul>
                                    @foreach (session('validationErrors') as $row => $messages)
                                        <li><strong>Row {{ $row }}:</strong>
                                            <ul>
                                                @foreach ($messages as $message)
                                                    @if (is_array($message))
                                                        @foreach ($message as $subMessage)
                                                            <li>{{ $subMessage }}</li>
                                                        @endforeach
                                                    @else
                                                        <li>{{ $message }}</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Display success message, if any -->
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <a href="{{ route('tcode_single_role.template') }}" class="btn btn-success mb-3">
                            Download Template Single Role - Tcode
                        </a>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form action="{{ route('tcode_single_role.preview') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Excel File *</label>
                                        <input type="file" name="excel_file" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Preview</button>
                                </form>
                            </div>
                        </div>

                        @if (session('tcodeParsed'))
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Preview</span>
                                    <button id="btnConfirmImport" class="btn btn-sm btn-primary">Confirm Import</button>
                                </div>
                                <div class="card-body">
                                    <table id="previewTable" class="table table-bordered table-sm w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Single Role</th>
                                                <th>Single Role Description</th>
                                                <th>Tcode</th>
                                                <th>Tcode Description</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    <div class="mt-3">
                                        <div class="progress">
                                            <div id="importProgress" class="progress-bar" style="width:0%">0%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if (session('tcodeParsed'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#previewTable').DataTable({
                    ajax: '{{ route('tcode_single_role.preview_data') }}',
                    processing: true,
                    serverSide: false,
                    paging: true,
                    searching: true,
                    columns: [{
                            data: 'single_role'
                        },
                        {
                            data: 'single_role_description'
                        },
                        {
                            data: 'tcode'
                        },
                        {
                            data: 'tcode_description'
                        }
                    ]
                });

                document.getElementById('btnConfirmImport').addEventListener('click', function() {
                    Swal.fire({
                        title: 'Confirm Import',
                        text: 'Proceed importing the displayed mappings?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                    }).then(res => {
                        if (!res.isConfirmed) return;

                        const bar = document.getElementById('importProgress');
                        fetch('{{ route('tcode_single_role.confirm') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(resp => {
                            const reader = resp.body.getReader();
                            const decoder = new TextDecoder();
                            let buffer = '';

                            function read() {
                                reader.read().then(({
                                    done,
                                    value
                                }) => {
                                    if (done) return;
                                    buffer += decoder.decode(value, {
                                        stream: true
                                    });
                                    const lines = buffer.split("\n");
                                    buffer = lines.pop();
                                    lines.forEach(line => {
                                        if (!line.trim()) return;
                                        try {
                                            const json = JSON.parse(line);
                                            if (json.progress !== undefined) {
                                                bar.style.width = json
                                                    .progress + '%';
                                                bar.textContent = json
                                                    .progress + '%';
                                            }
                                            if (json.success) {
                                                bar.classList.add('bg-success');
                                                Swal.fire('Done', json.success,
                                                    'success');
                                            }
                                            if (json.error) {
                                                bar.classList.add('bg-danger');
                                                Swal.fire('Error', json.error,
                                                    'error');
                                            }
                                        } catch (e) {}
                                    });
                                    read();
                                });
                            }
                            read();
                        });
                    });
                });
            });
        </script>
    @endif
@endsection
