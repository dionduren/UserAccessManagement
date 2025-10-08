@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h4 class="mb-0 flex-grow-1">Preview User System Data ({{ $rows->count() }})</h4>
                <div class="d-flex flex-wrap gap-2">
                    <form id="confirmForm" class="mb-3">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $session_id }}">
                        <input type="hidden" name="periode_id" value="{{ $periode_id }}">
                        <button type="submit" class="btn btn-success" id="confirmBtn">Confirm Import</button>
                    </form>
                </div>
            </div>
            <div class="card-body">

                <div class="progress mb-3" style="height:24px; display:none;" id="progressWrapper">
                    <div class="progress-bar" id="progressBar" style="width:0%">0%</div>
                </div>

                <div class="table-responsive">
                    <table id="userSystemPreviewTable" class="table table-striped table-bordered w-100">
                        <thead>
                            @php
                                // $columns passed from controller OR derive safely
                                if (!isset($columns)) {
                                    $firstRow = $rows->first();
                                    if (is_array($firstRow)) {
                                        $columns = array_keys($firstRow);
                                    } elseif ($firstRow instanceof \Illuminate\Support\Collection) {
                                        $columns = $firstRow->keys()->toArray();
                                    } else {
                                        $columns = [];
                                    }
                                }
                            @endphp
                            <tr>
                                @foreach ($columns as $col)
                                    <th>{{ $col }}</th>
                                @endforeach
                            </tr>
                            <tr class="dt-column-search">
                                @foreach ($columns as $i => $col)
                                    <th>
                                        <input type="text" class="form-control form-control-sm column-search"
                                            placeholder="Search {{ $col }}" data-col="{{ $i }}">
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                <tr>
                                    @foreach ($columns as $col)
                                        <td>{{ $r[$col] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#userSystemPreviewTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100, 250, 500],
                orderCellsTop: true,
                fixedHeader: true,
                responsive: true,
                deferRender: true,
                layout: {
                    top1Start: {
                        div: {
                            className: 'pageLength',
                        }
                    },
                    top1End: {
                        div: {
                            className: 'search',
                        }
                    },
                    bottom1Start: {
                        div: {
                            className: 'info',
                        }
                    },
                    bottom1End: {
                        div: {
                            className: 'paging',
                        }
                    }
                }
            });

            $('#userSystemPreviewTable thead').on('keyup change', '.column-search', function() {
                const colIndex = $(this).data('col');
                table.column(colIndex).search(this.value).draw();
            });

            const form = document.getElementById('confirmForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const progressWrapper = document.getElementById('progressWrapper');
                const progressBar = document.getElementById('progressBar');
                progressWrapper.style.display = 'block';
                progressBar.classList.remove('bg-danger', 'bg-success');
                progressBar.style.width = '1%';
                progressBar.textContent = 'Mulai...';

                const fd = new FormData(form);

                fetch("{{ route('user_system.import.confirm') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'text/event-stream'
                    },
                    body: fd
                }).then(response => {
                    const reader = response.body.getReader();
                    let buffer = '';

                    function read() {
                        return reader.read().then(({
                            done,
                            value
                        }) => {
                            if (done) {
                                if (progressBar.style.width !== '100%') {
                                    progressBar.classList.add('bg-danger');
                                    progressBar.textContent = 'Selesai (mungkin terputus)';
                                }
                                return;
                            }
                            buffer += new TextDecoder().decode(value);
                            const lines = buffer.split('\n');
                            buffer = lines.pop();
                            lines.forEach(line => {
                                if (!line.trim()) return;
                                try {
                                    const data = JSON.parse(line);
                                    if (data.progress !== undefined) {
                                        const pct = Math.min(100, parseInt(data
                                            .progress, 10));
                                        progressBar.style.width = pct + '%';
                                        progressBar.textContent = pct + '%';
                                    }
                                    if (data.error) {
                                        progressBar.classList.add('bg-danger');
                                        progressBar.textContent = 'Error';
                                        Swal.fire('Error', data.message ||
                                            'Gagal import', 'error');
                                    }
                                    if (data.success) {
                                        progressBar.style.width = '100%';
                                        progressBar.textContent = '100%';
                                        progressBar.classList.add('bg-success');
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil',
                                            text: data.message ||
                                                'Import selesai',
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                        if (data.redirect) {
                                            setTimeout(() => window.location.href =
                                                data.redirect, 1600);
                                        }
                                    }
                                } catch (e) {}
                            });
                            return read();
                        });
                    }
                    return read();
                }).catch(() => {
                    progressBar.classList.add('bg-danger');
                    progressBar.textContent = 'Error koneksi';
                    Swal.fire('Error', 'Koneksi gagal saat import', 'error');
                });
            });
        });
    </script>
@endsection
