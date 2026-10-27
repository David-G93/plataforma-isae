<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\AcademicStructureValidationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicQualityController extends Controller
{
    public function index(
        Request $request,
        AcademicStructureValidationService $validator,
    ): Response {
        abort_unless($request->user()?->can('academic.view'), 403);

        $academicYears = AcademicYear::query()
            ->orderByDesc('year')
            ->get(['id', 'year', 'name', 'is_active']);

        $selectedYear = (int) (
            $request->integer('year')
            ?: $academicYears->firstWhere('is_active', true)?->year
            ?: $academicYears->first()?->year
            ?: now()->year
        );

        return Inertia::render('Academic/Quality/Index', [
            'academicYears' => $academicYears,
            'selectedYear' => $selectedYear,
            'quality' => $validator->qualityControls($selectedYear),
        ]);
    }
}
