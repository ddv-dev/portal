<?php
// app/Http/Controllers/PhoneBookController.php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\Dept;
use App\Models\Corpus;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PhoneBookController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        $deptId = $request->input('dept_id');
        $corpusId = $request->input('corpus_id');
        $floor = $request->input('floor');

        // Получаем данные для меню
        try {
            $departments = Dept::with(['type', 'corpusDepts.corpus.building'])
                ->whereHas('deptWorkers', function ($query) {
                    $query->whereHas('worker', function ($q) {
                        $q->where('is_active', true);
                    });
                })
                ->orderBy('spr_order')
                ->get()
                ->groupBy('type.name');
        } catch (\Exception $e) {
            Log::error('Error loading departments: ' . $e->getMessage());
            $departments = collect();
        }

        try {
            $buildings = Building::with([
                'corpuses' => function ($query) {
                    $query->with([
                        'corpusDepts' => function ($q) {
                            $q->select('corpus_id', 'floor')
                                ->distinct()
                                ->orderBy('floor');
                        }
                    ])
                        ->whereHas('corpusDepts.dept.deptWorkers', function ($q) {
                            $q->whereHas('worker', function ($w) {
                                $w->where('is_active', true);
                            });
                        });
                }
            ])
                ->whereHas('corpuses.corpusDepts.dept.deptWorkers', function ($query) {
                    $query->whereHas('worker', function ($q) {
                        $q->where('is_active', true);
                    });
                })
                ->get();
        } catch (\Exception $e) {
            Log::error('Error loading buildings: ' . $e->getMessage());
            $buildings = collect();
        }

        $workersQuery = Worker::with([
            'deptWorkers.dept.type',
            'deptWorkers.corpusDept.corpus.building',
            'phones'
        ])
            ->where('is_active', true);

        if ($search) {
            $workersQuery->where(function ($query) use ($search) {
                $query->where('name_full', 'ilike', "%{$search}%")
                    ->orWhere('login_ad', 'ilike', "%{$search}%")
                    ->orWhereHas('phones', function ($q) use ($search) {
                        $q->where('phone_number', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('deptWorkers.dept', function ($q) use ($search) {
                        $q->where('name_full', 'ilike', "%{$search}%")
                            ->orWhere('name_short', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($deptId) {
            $workersQuery->whereHas('deptWorkers', function ($query) use ($deptId) {
                $query->where('dept_id', $deptId);
            });
        }

        if ($corpusId) {
            $workersQuery->whereHas('deptWorkers.corpusDept', function ($query) use ($corpusId) {
                $query->where('corpus_id', $corpusId);
            });
        }

        if ($floor) {
            $workersQuery->whereHas('deptWorkers.corpusDept', function ($query) use ($floor) {
                $query->where('floor', $floor);
            });
        }

        $workers = $workersQuery
            ->orderBy('spr_order')
            ->orderBy('name_full')->paginate(50);

        $corpuses = Corpus::with('building')->get();

        return view('directory', compact(
            'workers',
            'departments',
            'buildings',
            'corpuses',
            'search',
            'deptId',
            'corpusId',
            'floor'
        ));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $workers = Worker::with([
            'deptWorkers.dept',
            'deptWorkers.corpusDept.corpus.building',
            'phones'
        ])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name_full', 'ilike', "%{$query}%")
                    ->orWhere('login_ad', 'ilike', "%{$query}%")
                    ->orWhereHas('phones', function ($phoneQuery) use ($query) {
                        $phoneQuery->where('phone_number', 'ilike', "%{$query}%");
                    })
                    ->orWhereHas('deptWorkers.dept', function ($deptQuery) use ($query) {
                        $deptQuery->where('name_full', 'ilike', "%{$query}%")
                            ->orWhere('name_short', 'ilike', "%{$query}%");
                    });
            })
            ->limit(20)
            ->get()
            ->map(function ($worker) {
                return [
                    'id' => $worker->id,
                    'name' => $worker->name_full,
                    'position' => $worker->deptWorkers->first()?->position,
                    'department' => $worker->deptWorkers->first()?->dept?->name_full,
                    'phones' => $worker->phones->pluck('phone_number'),
                    'location' => $this->getWorkerLocation($worker),
                ];
            });

        return response()->json($workers);
    }

    /**
     * Получить локацию сотрудника
     */
    private function getWorkerLocation($worker)
    {
        $deptWorker = $worker->deptWorkers->first();
        if (!$deptWorker) {
            return null;
        }

        $corpusDept = $deptWorker->corpusDept;
        if (!$corpusDept) {
            return null;
        }

        $corpus = $corpusDept->corpus;
        $building = $corpus?->building;

        $location = [];
        if ($building) {
            $location[] = $building->name;
        }
        if ($corpus) {
            $location[] = $corpus->name;
        }
        if ($corpusDept->floor) {
            $location[] = "{$corpusDept->floor} этаж";
        }
        if ($corpusDept->sector) {
            $location[] = "сектор {$corpusDept->sector}";
        }

        return implode(' → ', $location);
    }
}