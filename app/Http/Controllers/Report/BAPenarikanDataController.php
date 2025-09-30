<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\Periode;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\VerticalJc;

class BAPenarikanDataController extends Controller
{
    public function index(Request $request)
    {
        $user_company = auth()->user()?->loginDetail?->company_code ?? null;
        if ($user_company && $user_company == 'A000') {

            $companies = Company::select('company_code', 'shortname', 'nama')
                ->orderBy('company_code')
                ->get();
        } else {
            $companies = Company::select('company_code', 'shortname', 'nama')
                ->where('company_code', $user_company)
                ->get();
        }

        return view('report.ba_penarikan.index', compact('companies'));
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 25);
        $syncdate = Periode::max('tanggal_create_periode');

        $allowedLengths = [10, 25, 50, 100, -1];
        if (!in_array($length, $allowedLengths)) {
            $length = 25;
        }

        $companyKey = trim((string) $request->get('company_id'));

        $company = null;
        if ($companyKey !== '') {
            $company = Company::where('shortname', $companyKey)
                ->orWhere('company_code', $companyKey)
                ->orWhere('nama', $companyKey)
                ->first();
        }

        $shortname   = $company?->shortname;
        $companyCode = $company?->company_code;
        $companyName = $company?->nama;

        // Base (all active generic users) – this defines the dataset scope
        $base = DB::table('mdb_usmm_master')
            ->where(function ($w) {
                $w->whereNull('valid_to')
                    ->orWhere('valid_to', '00000000')
                    ->orWhereRaw("to_date(valid_to,'YYYYMMDD') >= current_date");
            });
        // ->whereRaw("sap_user_id ~* '^[A-K]'");

        $recordsTotal = (clone $base)->count(); // total before company filter

        // Apply company filter (try match any representation stored in mdb_usmm_master.company)
        $filtered = clone $base;
        if ($shortname || $companyCode || $companyName) {
            $filtered->where(function ($q) use ($shortname, $companyCode, $companyName) {
                if ($shortname)   $q->orWhere('company', $shortname);
                if ($companyCode) $q->orWhere('company', $companyCode);
                if ($companyName) $q->orWhere('company', $companyName);
            });
        }

        $recordsFiltered = (clone $filtered)->count();

        // Ordering (must map displayed columns)
        // 0 No, 1 company, 2 user_type, 3 sap_user_id, 4 creator, 5 created_on, 6 valid_from, 7 valid_to, 8 last_logon_date, 9 last_logon_time
        $orderColIndex = (int) $request->input('order.0.column', 3);
        $orderDir      = $request->input('order.0.dir', 'asc');
        $orderDir      = $orderDir === 'desc' ? 'desc' : 'asc';

        $columnsMap = [
            0 => null,
            1 => 'company',
            2 => 'user_type',
            3 => 'sap_user_id',
            4 => 'creator',
            5 => 'creator_created_at',       // adjust if actual column differs
            6 => 'valid_from',
            7 => 'valid_to',
            8 => 'last_logon_date',
            9 => 'last_logon_time',
        ];
        $orderCol = $columnsMap[$orderColIndex] ?? 'sap_user_id';
        if ($orderCol) {
            $filtered->orderBy($orderCol, $orderDir);
        }

        if ($length !== -1) {
            $filtered->offset($start)->limit($length);
        }

        $rows = $filtered->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
            'summary' => [
                'company'               => $shortname ?? $companyCode ?? $companyName ?? '-',
                'total_active_generic'  => $recordsFiltered,
            ],
            'syncdate' => $syncdate,
            'company-name' => $companyName ?? '-'
        ]);
    }

    /** Export all (un-paginated) filtered rows to Word */
    public function exportWord(Request $request)
    {
        $companyKey = trim((string)$request->get('company_id'));
        $syncdate   = Periode::max('tanggal_create_periode');

        $company = null;
        if ($companyKey !== '') {
            $company = Company::where('shortname', $companyKey)->first();
        }
        $shortname   = $company?->shortname;
        $companyName = $company?->nama;

        // Base active generic users
        $query = DB::table('mdb_usmm_master')
            ->where(function ($w) {
                $w->whereNull('valid_to')
                    ->orWhere('valid_to', '00000000')
                    ->orWhereRaw("to_date(valid_to,'YYYYMMDD') >= current_date");
            });
        // ->whereRaw("sap_user_id ~* '^[A-K]'");

        if ($shortname) {
            $query->where(function ($q) use ($shortname) {
                if ($shortname)   $q->orWhere('company', $shortname);
            });
        }

        $rows = $query
            ->orderBy('sap_user_id')
            ->get();

        $total = $rows->count();
        $companyLabel = $companyName ?? '-';

        $titleWidth = 3500;
        $infoWidth = 8500;
        $signWidthA = 2500;
        $signWidthB = 3000;
        $signWidthC = 3000;

        $phpWord  = new PhpWord();
        $section = $phpWord->addSection(
            array(
                'marginLeft' => 600,
                'marginRight' => 600,
                'marginTop' => 600,
                'marginBottom' => 600
            )
        );

        // Set header for all pages
        $header = $section->addHeader();
        // Header Table (Logo + Title )
        $headerTable = $header->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $headerTable->addRow();
        $headerTable->addCell(3000, ['valign' => 'center'])->addImage(
            public_path('logo_pupuk_indonesia.png'),
            ['width' => 100, 'height' => 50, 'alignment' => Jc::CENTER]
        );
        $headerTable->addCell(9500, ['valign' => 'center', 'gridSpan' => 2])->addText(
            'Berita Acara Penarikan Data <w:br/> REVIU HAK AKSES',
            ['bold' => true, 'size' => 16],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        // $section->addText('BERITA ACARA PENARIKAN DATA - UID GENERIK SAP', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);
        // $section->addTextBreak(1);

        // Summary table
        $summaryTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 60]);
        $summaryTable->addRow(null);
        $summaryTable->addCell($titleWidth, ['valign' => 'center', 'bgColor' => 'D9D9D9'])->addText('D a t a', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $summaryTable->addCell($infoWidth, ['valign' => 'center', 'bgColor' => 'D9D9D9', 'gridSpan' => 3])->addText('I s i a n', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $summaryTable->addRow(null);
        $summaryTable->addCell($titleWidth, ['valign' => 'center'])->addText('Tanggal Penarikan Data', ['bold' => true, 'size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($infoWidth, ['valign' => 'center', 'gridSpan' => 3])->addText($this->formatSyncDate($syncdate), ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addRow(null);
        $summaryTable->addCell($titleWidth, ['valign' => 'center'])->addText('Tujuan', ['bold' => true, 'size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($infoWidth, ['valign' => 'center', 'gridSpan' => 3])->addText('Reviu User pengguna SAP', ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addRow(null);
        $summaryTable->addCell($titleWidth, ['valign' => 'center'])->addText('Lingkup', ['bold' => true, 'size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($infoWidth, ['valign' => 'center', 'gridSpan' => 3])->addText('Seluruh unit kerja pengguna SAP di ' . $companyLabel, ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addRow(null);
        $summaryTable->addCell($titleWidth, ['valign' => 'center'])->addText('Jumlah UID Aktif', ['bold' => true, 'size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($infoWidth, ['valign' => 'center', 'gridSpan' => 3])->addText((string)$total, ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addRow(null);
        $summaryTable->addCell($titleWidth, ['valign' => 'center'])->addText('Keterangan', ['bold' => true, 'size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($infoWidth, ['valign' => 'center', 'gridSpan' => 3])->addText('', ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addRow(3000, ['exactHeight' => true]);
        $summaryTable->addCell(12000, ['valign' => 'center', 'gridSpan' => 4])->addText('[MASUKKAN GAMBAR DI SINI]', ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addRow(null);
        $summaryTable->addCell($titleWidth, ['valign' => 'center', 'bgColor' => 'D9D9D9'])->addText('Diminta/Disetujui/Diproses', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $summaryTable->addCell($signWidthA, ['valign' => 'center', 'bgColor' => 'D9D9D9'])->addText('N a m a', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $summaryTable->addCell($signWidthB, ['valign' => 'center', 'bgColor' => 'D9D9D9'])->addText('Tanda Tangan', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $summaryTable->addCell($signWidthC, ['valign' => 'center', 'bgColor' => 'D9D9D9'])->addText('Tanggal', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $summaryTable->addRow(750, ['exactHeight' => true]);
        $summaryTable->addCell($titleWidth, ['valign' => 'center'])->addText('Staf System Admin/BASIS', ['bold' => true, 'size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($signWidthA, ['valign' => 'center'])->addText('', ['size' => 11], ['spaceAfter' => 0]);
        // $summaryTable->addCell($signWidthA, ['valign' => 'center'])->addText('Deny Pratama', ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($signWidthB, ['valign' => 'center'])->addText('', ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($signWidthC, ['valign' => 'center'])->addText($this->formatSyncDate($syncdate), ['size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $summaryTable->addRow(750, ['exactHeight' => true]);
        $summaryTable->addCell($titleWidth, ['valign' => 'center'])->addText('VP Operasional Sistem TI', ['bold' => true, 'size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($signWidthA, ['valign' => 'center'])->addText('', ['size' => 11], ['spaceAfter' => 0]);
        // $summaryTable->addCell($signWidthA, ['valign' => 'center'])->addText('Abdul Muhyi M', ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($signWidthB, ['valign' => 'center'])->addText('', ['size' => 11], ['spaceAfter' => 0]);
        $summaryTable->addCell($signWidthC, ['valign' => 'center'])->addText($this->formatSyncDate($syncdate), ['size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        $section->addTextBreak(1);

        // // Data table
        // $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 40]);
        // $headerStyle = ['bold' => true, 'size' => 8];
        // $cellHeader  = ['bgColor' => 'D9E1F2'];
        // $headers = ['No', 'Company', 'User Type', 'User ID', 'Creator', 'Created On', 'Valid From', 'Valid To', 'Last Logon Date', 'Last Logon Time'];
        // $table->addRow();
        // foreach ($headers as $h) {
        //     $table->addCell(1200, $cellHeader)->addText($h, $headerStyle, ['alignment' => Jc::CENTER]);
        // }

        // $i = 1;
        // foreach ($rows as $r) {
        //     $table->addRow();
        //     $table->addCell(600)->addText($i++, ['size' => 8]);
        //     $table->addCell(1400)->addText($this->safe($r->company), ['size' => 8]);
        //     $table->addCell(1400)->addText($this->safe(trim(($r->user_type ?? '') . ' ' . ($r->user_type_desc ?? ''))), ['size' => 8]);
        //     $table->addCell(1400)->addText($this->safe($r->sap_user_id), ['size' => 8]);
        //     $table->addCell(1400)->addText($this->safe($r->creator), ['size' => 8]);
        //     $table->addCell(1400)->addText($this->fmtDate($r->creator_created_at), ['size' => 8]);
        //     $table->addCell(1400)->addText($this->fmtDate($r->valid_from), ['size' => 8]);
        //     $table->addCell(1400)->addText($this->fmtDate($r->valid_to), ['size' => 8]);
        //     $table->addCell(1400)->addText($this->fmtDate($r->last_logon_date), ['size' => 8]);
        //     $table->addCell(1000)->addText($this->fmtTime($r->last_logon_time), ['size' => 8]);
        // }

        // if ($total === 0) {
        //     $table->addRow();
        //     $table->addCell(null, ['gridSpan' => 10])->addText('Tidak ada data', ['italic' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
        // }

        $fileName = 'BA-Penarikan-UID-Generik-' . $companyLabel . '-' . date('Ymd_His') . '.docx';
        $tmpPath  = tempnam(sys_get_temp_dir(), 'BA_GEN_');
        $realPath = $tmpPath . '.docx';

        IOFactory::createWriter($phpWord, 'Word2007')->save($realPath);

        return response()->download($realPath, $fileName)->deleteFileAfterSend(true);
    }

    // private function safe($v)
    // {
    //     if ($v === null || $v === '') return '-';
    //     return str_replace('&', 'dan', (string)$v);
    // }

    // private function fmtDate($v)
    // {
    //     if (!$v || $v === '00000000') return '-';
    //     $s = preg_replace('/\D/', '', $v);
    //     if (strlen($s) !== 8) return $v;
    //     $y = substr($s, 0, 4);
    //     $m = substr($s, 4, 2);
    //     $d = substr($s, 6, 2);
    //     if ($m === '00' || $d === '00') return '-';
    //     return $d . '-' . $m . '-' . $y;
    // }

    // private function fmtTime($v)
    // {
    //     if (!$v) return '-';
    //     $s = preg_replace('/\D/', '', $v);
    //     if ($s === '' || preg_match('/^0+$/', $s)) return '-';
    //     if (strlen($s) === 6) return substr($s, 0, 2) . ':' . substr($s, 2, 2) . ':' . substr($s, 4, 2);
    //     if (strlen($s) === 4) return substr($s, 0, 2) . ':' . substr($s, 2, 2) . ':00';
    //     if (strlen($s) === 2) return $s . ':00:00';
    //     return $v;
    // }

    private function formatSyncDate($v)
    {
        if (!$v) return '-';
        $s = preg_replace('/\D/', '', $v);
        if (strlen($s) < 8) return '-';

        $y = substr($s, 0, 4);
        $m = substr($s, 4, 2);
        $d = substr($s, 6, 2);

        if ($m === '00' || $d === '00') return '-';

        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        if (!isset($months[$m])) return '-';

        return $d . ' ' . $months[$m] . ' ' . $y;
    }
}
