<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SopController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Sop::query()->with('attachments')->where('is_active', true);

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                        ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            }

            if ($request->filled('year')){
                $query->whereYear('created_at', $request->input('year'));
            }

            $sops = $query->paginate(15);

            $years = Sop::selectRaw('EXTRACT(YEAR FROM created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');

        } catch (\Exception $e) {
            Log::error('Error loading sops: ' . $e->getMessage());
            $sops = collect();

        }

        return view('sop', compact(
            'sops',
            'years'
        ));
    }
}
