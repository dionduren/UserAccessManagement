@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">Upload Unit Kerja Master Data</h1>
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

                        <a href="{{ route('company_master_data.export') }}" target="_blank" class="btn btn-success mb-3">
                            Download Template Master Data Unit Kerja
                        </a>

                        <form id="upload-form" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Excel File</label>
                                <input type="file" name="excel_file" id="excel_file" class="form-control" required
                                    accept=".xlsx,.xls">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Start Upload
                            </button>
                        </form>

                        <div class="mt-4" id="upload-progress-container" style="display:none;">
                            <p>Uploading...</p>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped" id="upload-progress-bar" style="width: 0%">0%
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success mt-3 d-none" id="upload-complete">Upload complete</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#upload-form').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const xhr = new XMLHttpRequest();

                $('#upload-progress-container').show();
                $('#upload-progress-bar').width('0%').text('0%');
                $('#upload-complete').addClass('d-none');

                xhr.open('POST', "{{ route('unit-kerja.upload') }}", true);
                xhr.setRequestHeader('X-CSRF-TOKEN', $('input[name="_token"]').val());

                xhr.onprogress = function(e) {
                    const text = xhr.responseText.trim();
                    const lines = text.split('\n');
                    const lastLine = lines[lines.length - 1];

                    try {
                        const json = JSON.parse(lastLine);

                        if (json.progress !== undefined) {
                            $('#upload-progress-bar').css('width', json.progress + '%').text(json
                                .progress + '%');
                        }

                        if (json.success) {
                            $('#upload-progress-bar').css('width', '100%').text('100%');
                            $('#upload-complete').removeClass('d-none');
                        }
                    } catch (err) {
                        console.error('Progress JSON parse error', err);
                    }
                };

                xhr.send(formData);
            });
        });
    </script>
@endsection
