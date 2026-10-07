<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

/** Read-only audit trail. Entries can be filtered but never edited or deleted from the interface. */
class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $group = $request->query('group');
        $term = trim((string) $request->query('q'));

        $logs = ActivityLog::with('user:id,name,role')
            ->when($group, fn ($q) => $q->where('action', 'like', $group.'.%'))
            ->when($term, fn ($q) => $q->where(fn ($w) => $w->where('description', 'like', "%{$term}%")->orWhere('action', 'like', "%{$term}%")->orWhere('ip_address', 'like', "%{$term}%")))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity', [
            'logs' => $logs,
            'group' => $group,
            'term' => $term,
            'groups' => ['auth' => 'Sign-ins', 'two_factor' => 'Two-factor', 'sale' => 'Sales', 'commission' => 'Commissions', 'realtor' => 'Realtors', 'lead' => 'Leads', 'admin' => 'Content & settings', 'profile' => 'Profiles'],
        ]);
    }
}
