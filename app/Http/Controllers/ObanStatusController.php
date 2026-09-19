<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObanStatusController extends Controller
{
    protected array $states = [
        'available',
        'scheduled',
        'executing',
        'retryable',
        'cancelled',
        'completed',
        'discarded',
    ];

    public function dashboard()
    {
        return view('oban.dashboard');
    }

    public function status(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $page   = max(1, (int) $request->input('page', 1));
        $state  = (string) $request->input('state', 'all');
        $search = trim((string) $request->input('search', ''));

        $table = DB::table('oban_jobs');

        $summary = (clone $table)
            ->selectRaw('state, COUNT(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state');

        $jobsQuery = (clone $table)
            ->when($state !== 'all' && in_array($state, $this->states, true),
                fn ($q) => $q->where('state', $state))
            ->when($search !== '', function ($q) use ($search) {
                $like    = '%' . $search . '%';
                $argsCol = DB::getDriverName() === 'pgsql' ? DB::raw('args::text') : 'args';

                return $q->where(function ($inner) use ($like, $search, $argsCol) {
                    $inner
                        ->where('worker', 'like', $like)
                        ->orWhere('queue', 'like', $like)
                        ->orWhere($argsCol, 'like', $like);

                    if (ctype_digit($search)) {
                        $inner->orWhere('id', (int) $search);
                    }
                });
            });

        $total      = $jobsQuery->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $current    = min($page, $totalPages);

        $jobs = (clone $jobsQuery)
            ->orderByDesc('inserted_at')
            ->forPage($current, $perPage)
            ->get()
            ->map(function ($job) {
                $args = $job->args;
                if (is_string($args)) {
                    $args = json_decode($args, true);
                }

                return [
                    'id'           => $job->id,
                    'state'        => $job->state,
                    'queue'        => $job->queue,
                    'worker'       => class_basename(str_replace('Elixir.', '', $job->worker)),
                    'args'         => $args,
                    'attempt'      => $job->attempt,
                    'max_attempts' => $job->max_attempts,
                    'inserted_at'  => $job->inserted_at,
                    'scheduled_at' => $job->scheduled_at,
                    'attempted_at' => $job->attempted_at,
                    'completed_at' => $job->completed_at,
                    'discarded_at' => $job->discarded_at,
                ];
            });

        return response()->json([
            'summary'     => collect($this->states)->mapWithKeys(fn ($s) => [$s => $summary[$s] ?? 0]),
            'jobs'        => $jobs,
            'page'        => $current,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => $totalPages,
        ]);
    }
}