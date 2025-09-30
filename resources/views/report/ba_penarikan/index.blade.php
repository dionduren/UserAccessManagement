@extends('layouts.app')

@section('title', 'BA Penarikan Data (Active Generic Users)')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">Berita Acara Penarikan Data - REVIU HAK AKSES</h4>

        <form id="filter-form" class="row g-2 mb-3">
            <div class="col-sm-6 col-md-4">
                <label class="form-label mb-1">Company</label>
                <select name="company_id" id="company_id" class="form-select">
                    <option value="">-- All Companies --</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->shortname }}">
                            {{ $c->company_code }} - {{ $c->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                <button type="button" id="btn-reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>

        <div id="data-summary" style="display: none">
            <div class="card mb-3">
                <div class="card-header py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <strong>Data</strong>
                        </div>
                        <div class="col-md-6">
                            <strong>Isian</strong>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" id="btn-export-word" class="btn btn-sm btn-success" disabled>
                                Download Word
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="small text-muted">Tanggal Penarikan Data</div>
                        </div>
                        <div class="col-md-6">
                            <div id='syncdate' class="fw-semibold">
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="small text-muted">Tujuan</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold">Reviu User pengguna SAP</div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="small text-muted">Lingkup</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold">Seluruh unit kerja pengguna SAP di <span id="summary-company">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="small text-muted">Jumlah UID</div>
                        </div>
                        <div class="col-md-6">
                            <div id="summary-total" class="fw-semibold">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle nowrap table-responsive table-striped" id="data-table"
                style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Company</th>
                        <th>User ID</th>
                        <th>User Type</th>
                        <th>Valid From</th>
                        <th>Valid To</th>
                        <th>Last Logon Date</th>
                        <th>Last Logon Time</th>
                        <th>Creator</th>
                        <th>Created On</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="10" class="text-center text-muted">Klik Apply untuk memuat data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            const form = document.getElementById('filter-form');
            const resetBtn = document.getElementById('btn-reset');
            const companySelect = document.getElementById('company_id');
            const summaryWrap = document.getElementById('data-summary');
            const summaryTotal = document.getElementById('summary-total');
            const summaryCompany = document.getElementById('summary-company');
            const syncDate = document.getElementById('syncdate');
            let dt = null;

            /* ---------- Helpers ---------- */
            function showSummary() {
                if (summaryWrap.style.display !== 'block') {
                    summaryWrap.style.opacity = 0;
                    summaryWrap.style.display = 'block';
                    requestAnimationFrame(() => {
                        summaryWrap.style.transition = 'opacity .25s ease';
                        summaryWrap.style.opacity = 1;
                    });
                }
            }

            function hideSummary() {
                if (summaryWrap.style.display === 'block') {
                    summaryWrap.style.transition = 'opacity .2s ease';
                    summaryWrap.style.opacity = 0;
                    setTimeout(() => {
                        summaryWrap.style.display = 'none';
                        summaryWrap.style.removeProperty('opacity');
                    }, 200);
                }
            }

            function updateSummary(total) {
                summaryTotal.textContent = total ?? '-';
                const exportBtn = document.getElementById('btn-export-word');
                if (total && total > 0) {
                    showSummary();
                    exportBtn.disabled = false;
                } else {
                    hideSummary();
                    exportBtn.disabled = true;
                }
            }

            function setSyncDate(date) {
                syncDate.textContent = date ? formatYmdHisToIndo(date) : '-';
            }

            function setCompany(company) {
                summaryCompany.textContent = company ?? '-';
            }

            function formatYmdHisToIndo(val) {
                if (!val) return '-';
                const clean = String(val).replace(/[^\d]/g, '');
                if (clean.length < 8) return '-';
                const datePart = clean.slice(0, 8);
                const timePart = clean.slice(8); // may be 2 / 4 / 6 digits
                const dateStr = formatYmdToIndo(datePart);
                if (dateStr === '-') return '-';
                if (!timePart) return dateStr;
                const timeStr = formatTime(timePart);
                return timeStr === '-' ? dateStr : dateStr + ' - Pukul ' + timeStr + ' WIB';
            }

            function formatYmdToIndo(d) {
                if (!d || d === '00000000') return '-';
                if (d.length !== 8) return d;
                const y = d.slice(0, 4),
                    m = d.slice(4, 6),
                    day = d.slice(6, 8);
                if (m === '00' || day === '00') return '-';
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                    'Oktober', 'November', 'Desember'
                ];
                return parseInt(day, 10) + ' ' + months[parseInt(m, 10) - 1] + ' ' + y.substring(0);
            }

            function formatTime(val) {
                if (!val) return '-';
                let clean = String(val).replace(/\D/g, '');
                if (clean === '' || /^0+$/.test(clean)) return '-';
                if (clean.length === 6) return clean.slice(0, 2) + ':' + clean.slice(2, 4) + ':' + clean.slice(4, 6);
                if (clean.length === 4) return clean.slice(0, 2) + ':' + clean.slice(2, 4) + ':00';
                if (clean.length === 2) return clean + ':00:00';
                return val;
            }

            function initTable() {
                dt = $('#data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ordering: true,
                    deferRender: true,
                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ],
                    pageLength: 25,
                    ajax: {
                        url: "{{ route('report.ba_penarikan.data') }}",
                        data: function(d) {
                            d.company_id = companySelect.value;
                        }
                    },
                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                        },
                        {
                            data: 'company'
                        },
                        {
                            data: 'sap_user_id'
                        },
                        {
                            data: 'user_type',
                            render: (d, t, r) => [r.user_type, r.user_type_desc].filter(Boolean).join(
                                ' - ') || '-'
                        },
                        {
                            data: 'valid_from',
                            render: d => formatYmdToIndo(d)
                        },
                        {
                            data: 'valid_to',
                            render: d => formatYmdToIndo(d)
                        },
                        {
                            data: 'last_logon_date',
                            render: d => formatYmdToIndo(d)
                        },
                        {
                            data: 'last_logon_time',
                            render: d => formatTime(d)
                        },
                        {
                            data: 'creator'
                        },
                        {
                            data: 'creator_created_at',
                            render: d => formatYmdToIndo(d)
                        },
                    ],
                    order: [
                        [3, 'asc']
                    ],
                    drawCallback: function(settings) {
                        const json = settings.json;
                        if (json && json.summary) {
                            updateSummary(json.summary.total_active_generic);
                            setSyncDate(json.syncdate);
                            setCompany(json['company-name']);
                        }
                    },
                    language: {
                        emptyTable: 'Tidak ada data'
                    }
                });
            }

            /* ---------- Events ---------- */
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (dt) {
                    dt.ajax.reload(null, true); // reload & go to first page
                } else {
                    initTable();
                }
            });

            resetBtn.addEventListener('click', function() {
                form.reset();
                updateSummary(null);
                if (dt) {
                    dt.destroy();
                    dt = null;
                    $('#data-table tbody').html(
                        '<tr><td colspan="10" class="text-center text-muted">Klik Apply untuk memuat data</td></tr>'
                    );
                }
            });

            const exportBtn = document.getElementById('btn-export-word');
            exportBtn.addEventListener('click', function() {
                const company = companySelect.value;
                const url = "{{ route('report.ba_penarikan.export_word') }}" + (company ? ('?company_id=' +
                    encodeURIComponent(company)) : '');
                window.location = url;
            });

            // Start hidden
            hideSummary();
        })();
    </script>
@endsection
