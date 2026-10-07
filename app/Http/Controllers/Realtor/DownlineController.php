<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Sale;
use App\Models\User;
use App\Services\RealtorStats;
use Illuminate\Http\Request;

class DownlineController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $members = $user->downlines()
            ->withCount(['downlines', 'sales as closed_sales' => fn ($q) => $q->whereIn('status', ['approved', 'paid'])])
            ->latest()
            ->paginate(15);

        // What you earned from each direct recruit's whole line: their sales and everyone they brought in, at any depth.
        $earned = [];
        $teamSales = [];
        foreach ($members as $m) {
            $ids = $this->lineIds($m->id);
            $earned[$m->id] = (float) Commission::where('user_id', $user->id)
                ->whereHas('sale', fn ($q) => $q->whereIn('realtor_id', $ids)->whereIn('status', ['approved', 'paid']))
                ->sum('amount');
            $teamSales[$m->id] = Sale::whereIn('realtor_id', $ids)->whereIn('status', ['approved', 'paid'])->count();
        }

        return view('realtor.downline', [
            'members' => $members,
            'earned' => $earned,
            'teamSales' => $teamSales,
            'levels' => (new RealtorStats($user))->teamLevels(),
            // Commission that came from your team's sales (every tier above your own first tier).
            'teamEarned' => (float) Commission::where('user_id', $user->id)->where('tier', '>', 1)->sum('amount'),
            'sponsor' => $user->referrer,
        ]);
    }

    /** The member's id plus everyone below them, however deep the line goes. */
    private function lineIds(int $rootId): array
    {
        $ids = [$rootId];
        $frontier = [$rootId];
        for ($depth = 0; $depth < 30 && $frontier; $depth++) {
            $frontier = User::whereIn('referred_by', $frontier)->pluck('id')->all();
            array_push($ids, ...$frontier);
        }

        return $ids;
    }
}
