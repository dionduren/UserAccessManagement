<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Models\Kompartemen;
use App\Models\NIKJobRole;

use App\Models\Periode;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Writer\Word2007;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Element\Container;
use PhpOffice\PhpWord\Shared\XMLWriter;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\Style\TblWidth;

use Yajra\DataTables\DataTables;

class UARReportController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::select('company_code', 'nama')->get();

        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;

        $unitKerja = '';
        $unitKerjaName = '';

        if ($departemenId) {
            $departemen = Departemen::find($departemenId);
            $unitKerja = 'Departemen';
            $unitKerjaName = $departemen ? $departemen->nama : '';
        } elseif ($kompartemenId) {
            $kompartemen = Kompartemen::find($kompartemenId);
            $unitKerja = 'Kompartemen';
            $unitKerjaName = $kompartemen ? $kompartemen->nama : '';
        } elseif ($companyId) {
            $company = Company::where('company_code', $companyId)->first();
            $unitKerja = 'Company';
            $unitKerjaName = $company ? $company->nama : '';
        }

        // Count users
        $query = JobRole::query()
            ->with(['NIKJobRole' => function ($q) {
                $q->whereNull('deleted_at')->where('is_active', true);
            }]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($kompartemenId) {
            $query->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        $jobRoles = $query->get();
        $jumlahAwalUser = $jobRoles->reduce(function ($carry, $jobRole) {
            return $carry + $jobRole->NIKJobRole->count();
        }, 0);

        return view('report.uar.index', compact(
            'companies',
            'unitKerja',
            'unitKerjaName',
            'jumlahAwalUser'
        ));
    }

    public function getKompartemen(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemen = Kompartemen::where('company_id', $companyId)
            ->select('kompartemen_id', 'nama')
            ->orderBy('nama')
            ->get();
        return response()->json($kompartemen);
    }

    public function getDepartemen(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;

        if ($kompartemenId) {
            $departemen = Departemen::where('company_id', $companyId)
                ->where('kompartemen_id', $kompartemenId)
                ->select('departemen_id', 'nama')
                ->orderBy('nama')
                ->get();
        } else {
            $departemen = Departemen::where('company_id', $companyId)
                ->whereNull('kompartemen_id')
                ->select('departemen_id', 'nama')
                ->orderBy('nama')
                ->get();
        }
        return response()->json($departemen);
    }

    public function jobRolesData(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;

        $query = JobRole::query()
            ->with(['company', 'kompartemen', 'departemen', 'NIKJobRole.userGeneric'])
            ->whereHas('NIKJobRole', function ($q) {
                $q->whereNull('deleted_at')->where('is_active', true);
            });

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($kompartemenId) {
            $query->where('kompartemen_id', $kompartemenId);
        }
        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        $jobRoles = $query->get();
        $data = [];

        foreach ($jobRoles as $jobRole) {
            foreach ($jobRole->NIKJobRole as $nikJobRole) {
                $data[] = [
                    // Uncomment and adjust these if you want to show company/kompartemen/departemen columns
                    'company' => $jobRole->company->nama ?? '-',
                    'kompartemen' => $jobRole->kompartemen->nama ?? '-',
                    'departemen' => $jobRole->departemen->nama ?? '-',
                    'user_nik' => $nikJobRole->nik ?? '-',
                    'user_definisi' => $nikJobRole->definisi ?? '-',
                    'job_role' => $jobRole->nama ?? '-',
                ];
            }
        }

        return DataTables::of($data)->make(true);
    }

    public function exportWord(Request $request)
    {
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;

        // Get data as in index()
        $unitKerja = '-';
        $jabatanUnitKerja = '';
        $unitKerjaName = '';
        $latestPeriode = Periode::latest()->first()->definisi ?? '-';
        $nomorSurat = 'XXX'; // Example, replace with your logic

        if ($departemenId) {
            $departemen = Departemen::find($departemenId);
            $displayName = $departemen ? $departemen->nama : '';
            $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName); // Remove control chars
            if (preg_match('/^Dept\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Dept\.\s*/', '', $displayName);
                $unitKerja = 'Departemen ' . $displayName;
            } elseif (preg_match('/^Dep\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Dep\.\s*/', '', $displayName);
                $unitKerja = 'Departemen ' . $displayName;
            } else {
                $unitKerja = $displayName;
            }
            $unitKerja = $this->sanitizeForDocx($unitKerja);
            $unitKerjaName = $this->sanitizeForDocx($displayName);
            $jabatanUnitKerja = 'VP';
        } elseif ($kompartemenId) {
            $kompartemen = Kompartemen::find($kompartemenId);
            $displayName = $kompartemen ? $kompartemen->nama : '';
            $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName); // Remove control chars
            if (preg_match('/^Komp\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Komp\.\s*/', '', $displayName);
                $unitKerja = 'Kompartemen ' . $displayName;
            } elseif (preg_match('/^Fungs\.\s*/', $displayName)) {
                $displayName = preg_replace('/^Fungs\.\s*/', '', $displayName);
                $unitKerja = 'Fungsional ' . $displayName;
            } else {
                $unitKerja = $displayName;
            }
            $unitKerja = $this->sanitizeForDocx($unitKerja);
            $unitKerjaName = $this->sanitizeForDocx($displayName);
            $jabatanUnitKerja = 'SVP';
        } elseif ($companyId) {
            $company = Company::where('company_code', $companyId)->first();
            $displayName = $company ? $company->nama : '-';
            $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName); // Remove control chars
            $unitKerja = $this->sanitizeForDocx($displayName);
            $unitKerjaName = $this->sanitizeForDocx($displayName);
            $jabatanUnitKerja = 'Direktur';
        }

        // dd($unitKerja, $unitKerjaName, $jabatanUnitKerja, $latestPeriode, $nomorSurat);

        $query = JobRole::query()
            ->with(['NIKJobRole' => function ($q) {
                $q->whereNull('deleted_at')->where('is_active', true);
            }]);
        if ($companyId) $query->where('company_id', $companyId);
        if ($kompartemenId) $query->where('kompartemen_id', $kompartemenId);
        if ($departemenId) $query->where('departemen_id', $departemenId);

        $jobRoles = $query->get();
        $jumlahAwalUser = $jobRoles->reduce(function ($carry, $jobRole) {
            return $carry + $jobRole->NIKJobRole->count();
        }, 0);

        // Prepare PhpWord
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Set header for all pages
        $header = $section->addHeader();

        // Header Table (Logo + Title + Info)
        $headerTable = $header->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        // $headerTable = $header->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $headerTable->addRow();

        $headerTable->addCell(2000, ['valign' => 'center'])->addImage(
            public_path('logo_pupuk_indonesia.png'),
            ['width' => 80, 'height' => 40, 'alignment' => Jc::CENTER]
        );
        $headerTable->addCell(4500, ['gridSpan' => 2, 'valign' => 'center'])->addText(
            'REVIEW USER ID DAN OTORISASI',
            ['bold' => true, 'size' => 10],
            ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        );
        // Add a single row with a cell that spans 2 columns, containing a nested table for the 3 rows (Nomor, Periode, Hal. ke)
        $nestedTable = $headerTable->addCell(2000, ['gridSpan' => 2, 'valign' => 'center'])->addTable([
            'insideHBorderSize' => 6,
            'insideHBorderColor' => '000000',
            'insideVBorderSize' => 6,
            'insideVBorderColor' => '000000',
            'insideHBorder' => 'single',
            'insideVBorder' => 'single',
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
        ]);
        // Row 1: Nomor
        $nestedTable->addRow();
        $nestedTable->addCell(1000, ['valign' => 'center'])->addText('Nomor', ['size' => 8]);
        $nestedTable->addCell(2250, ['valign' => 'center'])->addText('PI-TIN-UAR-' . $nomorSurat, ['size' => 8]);

        // Row 2: Periode
        $nestedTable->addRow();
        $nestedTable->addCell(1000, ['valign' => 'center'])->addText('Periode', ['size' => 8]);
        $nestedTable->addCell(2250, ['valign' => 'center'])->addText($latestPeriode, ['size' => 8]);

        // Row 3: Hal. ke
        $nestedTable->addRow();
        $nestedTable->addCell(1000, ['valign' => 'center'])->addText('Hal. ke', ['size' => 8]);
        $nestedTable->addCell(2250, ['valign' => 'center'])->addPreserveText('{PAGE} dari {NUMPAGES}', ['size' => 8], ['alignment' => Jc::START]);

        // (No need to add the header table to the section body)
        $header->addTextBreak(1);
        // $section->addTextBreak(1);

        // Review Table
        $reviewTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        // First row with cellMargin
        $reviewTable->addRow();
        $reviewTable->addCell(12000, [
            'gridSpan' => 2,
            'bgColor' => 'D9E1F2',
            'valign' => 'center'
        ])->addText(
            'DOKUMEN REVIEW USER ID DAN OTORISASI',
            ['bold' => true, 'color' => '000000'],
            ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        );
        // Next rows without cellMargin
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Aset Informasi', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('User ID SAP', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Unit Kerja', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText($unitKerja ? "$unitKerja" : '-', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah Awal User', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText($jumlahAwalUser, ['italic' => true, 'size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah User Dihapus', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah User Baru', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addRow();
        $reviewTable->addCell(4000)->addText('Jumlah Akhir User', ['size' => 8], ['space' => ['after' => 0]]);
        $reviewTable->addCell(8000)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        $section->addTextBreak(1);

        // Job Role Table
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(12000, [
            'gridSpan' => 8,
            'bgColor' => 'D9E1F2',
            'valign' => 'center'
        ])->addText(
            'SUMMARY USER ACCESS REVIEW',
            ['bold' => true, 'color' => '000000', 'size' => 8],
            ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]
        );
        $table->addRow();
        $table->addCell(750, ['bgColor' => 'D9E1F2'])->addText('No', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(3000, ['bgColor' => 'D9E1F2'])->addText('User ID', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(3000, ['bgColor' => 'D9E1F2'])->addText('Nama', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(3000, ['bgColor' => 'D9E1F2'])->addText('Job Role', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(1500, ['bgColor' => 'D9E1F2'])->addText('NIK PIC', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(1500, ['bgColor' => 'D9E1F2'])->addText('Tetap', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(1500, ['bgColor' => 'D9E1F2'])->addText('Berubah', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);
        $table->addCell(2000, ['bgColor' => 'D9E1F2'])->addText('Keterangan', ['bold' => true, 'color' => '000000', 'size' => 8], ['alignment' => Jc::CENTER, 'space' => ['after' => 0]]);

        $no = 1;
        foreach ($jobRoles as $jobRole) {
            foreach ($jobRole->NIKJobRole as $nikJobRole) {
                $table->addRow();
                $table->addCell(750, ['valign' => 'center'])->addText(
                    $no++,
                    ['size' => 8],
                    ['space' => ['after' => 0], 'alignment' => Jc::CENTER]
                );
                $table->addCell(3000, ['valign' => 'center'])->addText($nikJobRole->nik ?? '-', ['size' => 8], ['space' => ['after' => 0]]);
                $table->addCell(3000, ['valign' => 'center'])->addText($nikJobRole->definisi ?? '-', ['size' => 8], ['space' => ['after' => 0]]);
                $table->addCell(3000, ['valign' => 'center'])->addText($jobRole->nama ?? '-', ['size' => 8], ['space' => ['after' => 0]]);
                $table->addCell(1500, ['valign' => 'center'])->addText('', ['size' => 8], ['space' => ['after' => 0]]);
                $table->addCell(1500, ['valign' => 'center'])->addText('X', ['size' => 12, 'color' => 'A6A6A6'], ['space' => ['after' => 0], 'alignment' => Jc::CENTER]);
                $table->addCell(1500, ['valign' => 'center'])->addText('-', ['size' => 8, 'color' => 'A6A6A6'], ['space' => ['after' => 0], 'alignment' => Jc::CENTER]);
                $table->addCell(2000, ['valign' => 'center'])->addText(
                    'Apabila ada (perubahan job function/nama/nik/Penonaktifan)',
                    ['size' => 8, 'color' => 'A6A6A6'],
                    [
                        'space' => ['after' => 0],
                        'wrap' => true,
                    ]
                );
            }
        }

        // Approval Table (Persetujuan)
        $section->addTextBreak(1);

        $approvalTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);

        // Header row
        $approvalTable->addRow();
        $approvalTable->addCell(5000, ['bgColor' => 'D9E1F2', 'gridSpan' => 2])->addText('Persetujuan', ['bold' => true], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500, ['bgColor' => 'D9E1F2'])->addText('Tanda Tangan', ['bold' => true], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500, ['bgColor' => 'D9E1F2'])->addText('Tanggal', ['bold' => true], ['space' => ['after' => 0]]);

        // Disiapkan oleh
        $approvalTable->addRow();
        $approvalTable->addCell(null, ['gridSpan' => 4])->addText('Disiapkan oleh:', ['bold' => true, 'size' => 8], ['space' => ['after' => 0]]);

        // // System Administrator
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText('System Administrator', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('Deny Pratama', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // // Functional Modul Sales & Distribution (SD)
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText('Functional Modul ....', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // Diverifikasi oleh
        $approvalTable->addRow();
        $approvalTable->addCell(null, ['gridSpan' => 4])->addText('Diverifikasi oleh:', ['bold' => true, 'size' => 8], ['space' => ['after' => 0]]);

        // VP Operasional Sistem TI
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText('VP Operasional Sistem TI', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('Abdul Muhyi Marakarma', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // VP Dept. Strategi & Evaluasi Kinerja
        $approvalTable->addRow();
        $approvalTable->addCell(2500)->addText($jabatanUnitKerja . ' ' . $unitKerjaName, ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);
        $approvalTable->addCell(2500)->addText('', ['size' => 8], ['space' => ['after' => 0]]);

        // Output
        // $fileName = 'Review UAR - ' . $unitKerja . ' - ' . date('Ymd') . '.docx';
        // $path = storage_path('app/public/' . $fileName);
        // $phpWord->save($path, 'Word2007', true);

        // return response()->download($path, $fileName, [
        //     'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        //     'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        // ])->deleteFileAfterSend(true);
        $fileName = 'Review UAR - ' . $unitKerja . ' - ' . date('Ymd') . '.docx';
        $filePath = storage_path('app/public/' . $fileName);

        // Save using IOFactory
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filePath);

        // Set headers for download
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");

        // Output file and delete after send
        readfile($filePath);
        unlink($filePath);
        exit;
    }

    public function exportWordFailed(Request $request)
    {
        // 1. Prepare data as in exportWordDone
        $companyId = $request->company_id;
        $kompartemenId = $request->kompartemen_id;
        $departemenId = $request->departemen_id;

        // $departemenName = Departemen::where('departemen_id', $departemenId)->first()->nama;
        // $unitKerja = $departemenName;
        $unitKerja = '';
        $jabatanUnitKerja = '';
        $unitKerjaName = '';
        $periode = Periode::latest()->first()->definisi ?? '-';
        $noSurat = '001'; // Example, replace with your logic

        // if ($departemenId) {
        //     $departemen = Departemen::find($departemenId);
        //     $displayName = $departemen ? $departemen->nama : '';
        //     $displayName = mb_convert_encoding($displayName, 'UTF-8', 'UTF-8');
        //     $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName); // Remove control chars
        //     if (preg_match('/^Dept\.\s*/', $displayName)) {
        //         $displayName = preg_replace('/^Dept\.\s*/', '', $displayName);
        //         $unitKerja = 'Departemen ' . $displayName;
        //     } elseif (preg_match('/^Dep\.\s*/', $displayName)) {
        //         $displayName = preg_replace('/^Dep\.\s*/', '', $displayName);
        //         $unitKerja = 'Departemen ' . $displayName;
        //     } else {
        //         $unitKerja = $displayName;
        //     }
        //     $unitKerja = $this->sanitizeForDocx($unitKerja);
        //     $unitKerjaName = $this->sanitizeForDocx($displayName);
        //     $jabatanUnitKerja = 'VP';
        // } elseif ($kompartemenId) {
        //     $kompartemen = Kompartemen::find($kompartemenId);
        //     $displayName = $kompartemen ? $kompartemen->nama : '';
        //     $displayName = mb_convert_encoding($displayName, 'UTF-8', 'UTF-8');
        //     $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName); // Remove control chars
        //     if (preg_match('/^Komp\.\s*/', $displayName)) {
        //         $displayName = preg_replace('/^Komp\.\s*/', '', $displayName);
        //         $unitKerja = 'Kompartemen ' . $displayName;
        //     } elseif (preg_match('/^Fungs\.\s*/', $displayName)) {
        //         $displayName = preg_replace('/^Fungs\.\s*/', '', $displayName);
        //         $unitKerja = 'Fungsional ' . $displayName;
        //     } else {
        //         $unitKerja = $displayName;
        //     }
        //     $unitKerja = $this->sanitizeForDocx($unitKerja);
        //     $unitKerjaName = $this->sanitizeForDocx($displayName);
        //     $jabatanUnitKerja = 'SVP';
        // } elseif ($companyId) {
        //     $company = Company::where('company_code', $companyId)->first();
        //     $displayName = $company ? $company->nama : '-';
        //     $displayName = mb_convert_encoding($displayName, 'UTF-8', 'UTF-8');
        //     $displayName = preg_replace('/[^\P{C}\n]+/u', '', $displayName); // Remove control chars
        //     $unitKerja = $this->sanitizeForDocx($displayName);
        //     $unitKerjaName = $this->sanitizeForDocx($displayName);
        //     $jabatanUnitKerja = 'Direktur';
        // }

        // dd($unitKerja, $unitKerjaName, $jabatanUnitKerja, $periode, $noSurat);

        // $query = JobRole::query()
        //     ->with(['NIKJobRole' => function ($q) {
        //         $q->whereNull('deleted_at')->where('is_active', true);
        //     }]);
        // if ($companyId) $query->where('company_id', $companyId);
        // if ($kompartemenId) $query->where('kompartemen_id', $kompartemenId);
        // if ($departemenId) $query->where('departemen_id', $departemenId);

        // $jobRoles = $query->get();
        // $jumlahAwalUser = $jobRoles->reduce(function ($carry, $jobRole) {
        //     return $carry + $jobRole->NIKJobRole->count();
        // }, 0);

        // // 2. Prepare rows for SUMMARY USER ACCESS REVIEW
        // $rows = [];
        // $no = 1;
        // foreach ($jobRoles as $jobRole) {
        //     foreach ($jobRole->NIKJobRole as $nikJobRole) {
        //         $rows[] = [
        //             'no' => $no++,
        //             'user_id' => $nikJobRole->nik ?? '-',
        //             'nama' => $nikJobRole->definisi ?? '-',
        //             'job_role' => $jobRole->nama ?? '-',
        //             'nik_pic' => '', // Fill as needed
        //             'tetap' => 'X',
        //             'berubah' => '-',
        //             'keterangan' => 'Apabila ada (perubahan job function/nama/nik/Penonaktifan)',
        //         ];
        //     }
        // }

        // 3. Load template
        $templatePath = base_path('resources/templates/template-uar.docx');
        $templateProcessor = new TemplateProcessor($templatePath);

        // 4. Set single values
        $templateProcessor->setValue('unit_kerja', $unitKerja);
        // $templateProcessor->setValue('jumlah_awal_user', $jumlahAwalUser);
        $templateProcessor->setValue('periode', $periode);
        $templateProcessor->setValue('jabatan_unit_kerja', $jabatanUnitKerja);
        $templateProcessor->setValue('nama_unit_kerja', $unitKerjaName);
        $templateProcessor->setValue('no_surat', $noSurat);

        // 5. Clone table rows for SUMMARY USER ACCESS REVIEW
        // if (count($rows) > 0) {
        //     $templateProcessor->cloneRowAndSetValues('no', $rows);
        // } else {
        //     // If no data, clear the row
        // $templateProcessor->setValue('no', '');
        // $templateProcessor->setValue('user_id', '');
        // $templateProcessor->setValue('nama', '');
        // $templateProcessor->setValue('job_role', '');
        // $templateProcessor->setValue('nik_pic', '');
        // $templateProcessor->setValue('tetap', '');
        // $templateProcessor->setValue('berubah', '');
        // $templateProcessor->setValue('keterangan', '');
        // }

        // 6. Save and download
        $fileName = 'uar_report_' . date('Ymd_His') . '.docx';
        $tempPath = storage_path('app/tmp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }
        $fullPath = $tempPath . '/' . $fileName;
        $templateProcessor->saveAs($fullPath);

        // return response()->download($fullPath, $fileName, [
        //     'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        //     'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        // ])->deleteFileAfterSend(true);
        return response()->download($fullPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function sanitizeForDocx($string)
    {
        // // Remove control characters except newline and tab
        // $string = preg_replace('/[^\P{C}\n\t]+/u', '', $string);
        // // Convert to UTF-8 if not already
        // $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        // // Encode XML special chars
        // $string = htmlspecialchars($string, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $string = str_replace('&', 'dan', $string);
        return $string;
    }
}
