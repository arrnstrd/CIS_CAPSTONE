<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    //
    public function store(Request $request)
    {
        $validateData = $request->validate([
             'student_id' => ['required', 'exists:students,id'],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'level' => ['required' , 'in:elementary,hs,shs'],
            'grade_level' => [
                'required',
                Rule::in([
                    'Grade 1','Grade 2','Grade 3','Grade 4',
                    'Grade 5','Grade 6','Grade 7','Grade 8',
                    'Grade 9','Grade 10','Grade 11','Grade 12',
                ])
            ],

            'section' => ['required', 'string', 'max:255'],
            'session_type' => ['required', 'in:morning,afternoon'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        

        try{
            $enrollment = Enrollment::create($validateData);

          return response()->json([
            'message' => 'Enrollment created successfully',
            'data' => $enrollment
          ]);
        }
        catch(QueryException $e){
            return response()->json([
                'message' => 'Student is already enrolled for this school year'
            ] , 422);
        }          
    }



    public function update(Request $request, string $id){
        $enrollment = Enrollment::findOrFail($id);

        $validatedData = $request->validate([
             'student_id' => ['required', 'exists:students,id'],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'level' => ['required' , 'in:elementary,hs,shs'],
            'grade_level' => [
                'required',
                Rule::in([
                    'Grade 1','Grade 2','Grade 3','Grade 4',
                    'Grade 5','Grade 6','Grade 7','Grade 8',
                    'Grade 9','Grade 10','Grade 11','Grade 12',
                ])
            ],

            'section' => ['required', 'string', 'max:255'],
            'session_type' => ['required', 'in:morning,afternoon'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        //check if exists
       $exists = Enrollment::where('student_id', $validatedData['student_id'])
                    ->where('school_year', $validatedData['school_year'])
                    ->where('id', '!=', $id)
                    ->exists();

        if($exists){
            return response()->json([
                'message' => 'This student is already enrolled for this school year'
            ] , 422);
        }
        

        $enrollment->update($validatedData);


        return response()->json([
            'message' => 'Enrollment updated successfully',
            'data' => $enrollment
        ]);
    }

    public function destroy(string $id)
{
    $enrollment = Enrollment::find($id);

    if (!$enrollment) {
        return response()->json([
            'message' => 'Enrollment not found'
        ], 404);
    }

    try {
        $enrollment->delete();

        return response()->json([
            'message' => 'Enrollment deleted successfully',
            'data' => [
                'id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'school_year' => $enrollment->school_year,
                'level' => $enrollment->level,
                'status' => 'deleted'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete enrollment',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    


}
