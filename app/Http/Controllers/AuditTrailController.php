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

        // Apply filters from filter form
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

        // Exclude menu_access if checkbox is checked
        if ($request->filled('exclude_menu_access') && $request->exclude_menu_access == 1) {
            $query->where('activity_type', '!=', 'menu_access');
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                // Column search functionality
                if ($request->has('columns')) {
                    // Column 0: ID
                    if (!empty($request->columns[0]['search']['value'])) {
                        $query->where('id', 'like', '%' . $request->columns[0]['search']['value'] . '%');
                    }
                    // Column 1: Date
                    if (!empty($request->columns[1]['search']['value'])) {
                        $query->where('logged_at', 'like', '%' . $request->columns[1]['search']['value'] . '%');
                    }
                    // Column 2: User
                    if (!empty($request->columns[2]['search']['value'])) {
                        $searchValue = $request->columns[2]['search']['value'];
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('username', 'like', '%' . $searchValue . '%')
                                ->orWhereHas('user', function ($qu) use ($searchValue) {
                                    $qu->where('name', 'like', '%' . $searchValue . '%');
                                });
                        });
                    }
                    // Column 3: Activity
                    if (!empty($request->columns[3]['search']['value'])) {
                        $query->where('activity_type', 'like', '%' . $request->columns[3]['search']['value'] . '%');
                    }
                    // Column 4: Model
                    if (!empty($request->columns[4]['search']['value'])) {
                        $query->where('model_type', 'like', '%' . $request->columns[4]['search']['value'] . '%');
                    }
                    // Column 5: Model ID
                    if (!empty($request->columns[5]['search']['value'])) {
                        $query->where('model_id', 'like', '%' . $request->columns[5]['search']['value'] . '%');
                    }
                    // Column 6: IP Address
                    if (!empty($request->columns[6]['search']['value'])) {
                        $query->where('ip_address', 'like', '%' . $request->columns[6]['search']['value'] . '%');
                    }
                    // Column 7: Route
                    if (!empty($request->columns[7]['search']['value'])) {
                        $query->where('route', 'like', '%' . $request->columns[7]['search']['value'] . '%');
                    }
                }
            })
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
