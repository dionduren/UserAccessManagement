@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-lg-8">
                <h4 class="mb-2">Sinkronisasi User ID (USMM)</h4>
                <div class="alert alert-info small mb-2">
                    <strong>Penjelasan:</strong><br>
                    Data diambil dari tabel middle <code>mdb_usmm_master</code> lalu dipisah menjadi dua:
                    <ul class="mb-1">
                        <li><strong>userNIK</strong>: <code>sap_user_id</code> diawali angka (user_type = NIK)</li>
                        <li><strong>userGeneric</strong>: selain itu (user_type = Generic)</li>
                    </ul>
                    Default hanya menarik user yang masih aktif: <code>valid_to</code> NULL / 00000000 / >= hari ini.<br>
                    Opsi <em>Termasuk Expired</em> akan memasukkan seluruh data tanpa filter aktif.<br>
                    Setiap proses <span class="text-danger">melakukan TRUNCATE</span> kedua tabel lokal sebelum insert ulang.
                </div>
                <div class="card shadow-sm">
                    <div class="card-header py-2">
                        <strong>Form Sinkronisasi</strong>
                    </div>
                    <div class="card-body">
                        <form id="form-sync" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Periode</label>
                                <select name="periode_id" id="periode_id" class="form-select form-select-sm" required>
                                    <option value="">-- pilih --</option>
                                    @foreach ($periodes as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->definisi }} @if ($p->is_active)
                                                (Aktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Batch Size</label>
                                <input type="number" min="100" max="5000" value="1000" name="batch"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 d-flex align-items-end gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-sm" data-action="default">
                                    Sync Aktif (Default)
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" data-action="refresh">
                                    Refresh Middle + Sync
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" data-action="expired">
                                    Sync Semua (Termasuk Expired)
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-action="refresh_expired">
                                    Refresh + Semua (Expired Termasuk)
                                </button>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="show-raw">
                                    <label class="form-check-label" for="show-raw">Tampilkan JSON mentah</label>
                                </div>
                            </div>
                        </form>
                        <hr class="my-3">
                        <div id="status-area" class="small text-muted">
                            Belum ada proses berjalan.
                        </div>
                        <pre id="raw-output" class="mt-2 d-none bg-dark text-light p-2 small" style="max-height:300px;overflow:auto;"></pre>
                        <div id="summary-cards" class="row g-2 mt-1 d-none"></div>
                    </div>
                </div>
                <div class="card shadow-sm mt-3">
                    <div class="card-header py-2">
                        <strong>Keterangan Kolom Penting</strong>
                    </div>
                    <div class="card-body small">
                        <ul class="mb-0">
                            <li><code>license_type</code>: jenis lisensi (contractual_user_type).</li>
                            <li><code>valid_from / valid_to</code>: periode aktif user.</li>
                            <li><code>last_login</code>: gabungan tanggal + waktu logon (khusus Generic jika tersedia).</li>
                            <li><code>group</code>: kode perusahaan / company dari sumber.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header py-2">
                        <strong>Ringkasan Proses Terakhir</strong>
                    </div>
                    <div class="card-body small" id="last-summary">
                        Belum ada.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay Loading -->
    <div id="loader-overlay" style="position:fixed;inset:0;display:none;background:rgba(255,255,255,.7);z-index:1050;">
        <div class="d-flex h-100 w-100 align-items-center justify-content-center">
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div class="small fw-semibold">Memproses, mohon tunggu...</div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            const endpoint = "{{ route('import.user_id.sync') }}";
            const form = document.getElementById('form-sync');
            const statusArea = document.getElementById('status-area');
            const rawOut = document.getElementById('raw-output');
            const summaryCards = document.getElementById('summary-cards');
            const lastSummary = document.getElementById('last-summary');
            const overlay = document.getElementById('loader-overlay');
            const showRaw = document.getElementById('show-raw');

            function setLoading(state) {
                overlay.style.display = state ? 'block' : 'none';
            }

            function buildQuery(action) {
                const fd = new FormData(form);
                const params = new URLSearchParams();
                const periode = fd.get('periode_id');
                if (!periode) {
                    alert('Pilih periode terlebih dahulu.');
                    return null;
                }
                params.set('periode_id', periode);
                params.set('batch', fd.get('batch') || 1000);
                switch (action) {
                    case 'refresh':
                        params.set('refresh', 1);
                        break;
                    case 'expired':
                        params.set('include_expired', 1);
                        break;
                    case 'refresh_expired':
                        params.set('refresh', 1);
                        params.set('include_expired', 1);
                        break;
                }
                return params.toString();
            }

            function card(label, value, bg = 'primary') {
                return `<div class="col-6 col-md-4">
            <div class="card border-${bg} border-1">
              <div class="card-body py-2 px-2">
                <div class="small text-muted">${label}</div>
                <div class="fw-semibold">${value}</div>
              </div>
            </div>
        </div>`;
            }

            async function run(action) {
                const qs = buildQuery(action);
                if (!qs) return;
                const url = endpoint + '?' + qs;
                statusArea.textContent = 'Mengirim permintaan: ' + url;
                rawOut.classList.toggle('d-none', !showRaw.checked);
                rawOut.textContent = '';
                summaryCards.innerHTML = '';
                summaryCards.classList.add('d-none');
                setLoading(true);
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });
                    const data = await res.json();
                    setLoading(false);
                    if (!res.ok) {
                        statusArea.innerHTML = '<span class="text-danger">Gagal: ' + (data.message || res.status) +
                            '</span>';
                    } else {
                        statusArea.innerHTML = '<span class="text-success">' + (data.message || 'Selesai') +
                            '</span>';
                    }
                    if (showRaw.checked) {
                        rawOut.textContent = JSON.stringify(data, null, 2);
                    }
                    if (data.summary) {
                        const s = data.summary;
                        summaryCards.innerHTML =
                            card('Sumber Awal', s.source_total ?? '-') +
                            card('Setelah Filter', s.source_after_filter ?? '-') +
                            card('Generic Baru', s.insert_generic ?? '-', 'success') +
                            card('NIK Baru', s.insert_nik ?? '-', 'success') +
                            card('Expired Terlewat', s.skipped_expired ?? '-', 'warning') +
                            card('Refresh?', s.refreshed ? 'Ya' : 'Tidak', s.refreshed ? 'info' : 'secondary');
                        summaryCards.classList.remove('d-none');
                        lastSummary.innerHTML = `
                    <div><strong>Terakhir:</strong> ${new Date().toLocaleString()}</div>
                    <div>Total Sumber: <code>${s.source_total}</code></div>
                    <div>Setelah Filter: <code>${s.source_after_filter}</code></div>
                    <div>Generic Insert: <code>${s.insert_generic}</code></div>
                    <div>NIK Insert: <code>${s.insert_nik}</code></div>
                    <div>Expired (dihitung): <code>${s.skipped_expired}</code></div>
                    <div>Refresh Middle: <code>${s.refreshed ? 'Ya':'Tidak'}</code></div>
                `;
                    }
                } catch (e) {
                    setLoading(false);
                    statusArea.innerHTML = '<span class="text-danger">Error JS: ' + e.message + '</span>';
                }
            }

            form.querySelectorAll('button[data-action]').forEach(btn => {
                btn.addEventListener('click', () => run(btn.dataset.action));
            });

        })();
    </script>
@endsection
