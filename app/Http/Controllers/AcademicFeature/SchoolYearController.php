<?php

namespace App\Http\Controllers\AcademicFeature;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class SchoolYearController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'school_year' => ['required', 'string', 'unique:school_years,school_year'],
            'is_active' => ['required', 'boolean']
        ]);

        try {

            $schoolYear = DB::transaction(function () use ($validatedData) {

                if ($validatedData['is_active']) {
                    SchoolYear::where('is_active', true)
                        ->update([
                            'is_active' => false
                        ]);
                }

                return SchoolYear::create($validatedData);
            });

            return response()->json([
                'message' => 'School year created successfully',
                'data' => $schoolYear
            ]);

        } catch (QueryException $e) {

            return response()->json([
                'message' => 'Unable to create school year'
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong while creating the school year'
            ], 500);

        }
    }

    public function update(Request $request, string $id)
    {
        $schoolYear = SchoolYear::findOrFail($id);

        $validatedData = $request->validate([
            'school_year' => [
                'required',
                'string',
                Rule::unique('school_years', 'school_year')
                    ->ignore($schoolYear->id)
            ],
            'is_active' => ['required', 'boolean']
        ]);

        try {

            DB::transaction(function () use ($validatedData, $schoolYear) {

                if ($validatedData['is_active']) {
                    SchoolYear::where('is_active', true)
                        ->where('id', '!=', $schoolYear->id)
                        ->update([
                            'is_active' => false
                        ]);
                }

                $schoolYear->update($validatedData);
            });

            return response()->json([
                'message' => 'School year information updated successfully',
                'data' => $schoolYear->fresh()
            ]);

        } catch (QueryException $e) {

            return response()->json([
                'message' => 'Failed to update school year'
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong while updating the school year'
            ], 500);

        }
    }

    public function destroy(string $id)
    {
        $schoolYear = SchoolYear::findOrFail($id);

        if (!$schoolYear->is_active) {
            return response()->json([
                'message' => 'The school year is already inactive',
                'data' => $schoolYear
            ]);
        }

        try {

            $schoolYear->update([
                'is_active' => false
            ]);

            return response()->json([
                'message' => 'School year archived successfully',
                'data' => $schoolYear->fresh()
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to archive school year'
            ], 500);

        }
    }

    public function restore(string $id)
    {
        $schoolYear = SchoolYear::findOrFail($id);

        if ($schoolYear->is_active) {
            return response()->json([
                'message' => 'The selected school year is already active',
                'data' => $schoolYear
            ]);
        }

        try {

            DB::transaction(function () use ($schoolYear) {

                SchoolYear::where('is_active', true)
                    ->where('id', '!=', $schoolYear->id)
                    ->update([
                        'is_active' => false
                    ]);

                $schoolYear->update([
                    'is_active' => true
                ]);
            });

            return response()->json([
                'message' => 'School year restored successfully',
                'data' => $schoolYear->fresh()
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to restore the selected school year'
            ], 500);

        }
    }
}
