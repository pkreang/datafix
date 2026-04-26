<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\HasPerPage;
use App\Http\Controllers\Controller;
use App\Models\SystemChangeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemChangeLogController extends Controller
{
    use HasPerPage;

    /**
     * Super-admin-only view of the audit trail across all configurable
     * surfaces (settings, workflow stages, document types, roles,
     * permissions). Filters: entity_type, actor (by id), date range.
     */
    public function index(Request $request): View
    {
        $entityType = (string) $request->query('entity_type', '');
        $actorId = (int) $request->query('actor_user_id', 0);
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $query = SystemChangeLog::query()->with('actor');

        if ($entityType !== '') {
            $query->where('entity_type', $entityType);
        }
        if ($actorId > 0) {
            $query->where('actor_user_id', $actorId);
        }
        if ($from !== '') {
            $query->where('created_at', '>=', $from);
        }
        if ($to !== '') {
            // Inclusive end-of-day for date-only inputs.
            $query->where('created_at', '<=', $to.' 23:59:59');
        }

        $perPage = $this->resolvePerPage($request, 'system_change_log_per_page');
        $logs = $query->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $entityTypes = ['setting', 'workflow_stage', 'document_type', 'role', 'permission'];
        $actors = User::query()
            ->whereIn('id', SystemChangeLog::query()->whereNotNull('actor_user_id')->distinct()->pluck('actor_user_id'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('settings.system-change-log', [
            'logs' => $logs,
            'entityTypes' => $entityTypes,
            'actors' => $actors,
            'filters' => compact('entityType', 'actorId', 'from', 'to'),
            'perPage' => $perPage,
        ]);
    }
}
