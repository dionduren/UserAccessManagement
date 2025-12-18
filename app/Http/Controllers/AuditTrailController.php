<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AuditTrailController extends Controller
{
    public function index()
    {
        return view('audit-trails.index');
    }

    public function getData(Request $request)
    {
        $query = AuditTrail::with('user')->orderBy('logged_at', 'desc');

        // Apply filters
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('logged_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('logged_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addColumn('user_name', function ($log) {
                return $log->user ? $log->user->name : ($log->username ?? 'System');
            })
            ->addColumn('model_name', function ($log) {
                if ($log->model_type) {
                    $parts = explode('\\', $log->model_type);
                    return end($parts);
                }
                return '-';
            })
            ->addColumn('changes', function ($log) {
                $html = '';
                if ($log->before_data || $log->after_data) {
                    $html .= '<button class="btn btn-sm btn-info view-changes" 
                                data-before="' . htmlspecialchars(json_encode($log->before_data), ENT_QUOTES) . '"
                                data-after="' . htmlspecialchars(json_encode($log->after_data), ENT_QUOTES) . '">
                                <i class="bi bi-eye"></i> View
                            </button>';
                }
                return $html;
            })
            ->editColumn('logged_at', function ($log) {
                return $log->logged_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['changes'])
            ->make(true);
    }

    public function show(AuditTrail $auditTrail)
    {
        $auditTrail->load('user');
        return view('audit-trails.show', compact('auditTrail'));
    }
}
