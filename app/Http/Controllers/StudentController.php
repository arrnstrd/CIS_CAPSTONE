<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\QrCode;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class StudentController extends Controller
{

    public function index(Request $request)
    {
        $query  = $request->input('query');
        $status = $request->input('status');
        $sex    = $request->input('sex');

        $students = Student::when($query, function ($q) use ($query) {
            $q->where('student_number', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
        })
            ->when($status && $status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($sex && $sex !== 'all', function ($q) use ($sex) {
                $q->where('sex', $sex);
            })
            ->orderBy('student_number', 'desc')
            ->paginate(25)
            ->withQueryString();



        return view('admin-modules.management.studentList', compact('students'));
    }

    public function store(Request $request)
    {
        // validate input from form (frontend)
        $validatedData = $request->validate([
            'lrn' => ['required', 'digits:12', 'unique:students,lrn'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'middle_name' => ['nullable', 'string'],
            'sex' => ['required', 'in:female,male'],
            'address' => ['required', 'string'],
            'birthdate' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],

            // guardian input
            'name' => ['required', 'string'],
            'relationship' => ['required', 'in:mother,father,sibling,guardian'],
            'email' => ['required', 'email']
        ]);


        $student = DB::transaction(function () use ($validatedData) {

            // 1. create student first (without student_number)
            $student = Student::create([
                'lrn' => $validatedData['lrn'],
                'first_name' => strip_tags($validatedData['first_name']),
                'last_name' => strip_tags($validatedData['last_name']),

                'middle_name' => isset($validatedData['middle_name'])
                    ? strip_tags($validatedData['middle_name'])
                    : null,

                'sex'  => $validatedData['sex'],
                'address' => strip_tags($validatedData['address']),
                'birthdate' => $validatedData['birthdate'],
                'status' => $validatedData['status'],
            ]);

            // // 2. generate student number using real DB ID
            // $studentNumber = 'STU-' . now()->year . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);

            // $student->update([
            //     'student_number' => $studentNumber
            // ]);


            

            // 3. create guardian linked to student
            Guardian::create([
                'student_id' => $student->id,
                'name' => strip_tags($validatedData['name']),
                'relationship' => $validatedData['relationship'],
                'email' => strip_tags($validatedData['email']),
            ]);

            //4. generate Unique qr code 
            do {
                $qrCode = Str::upper(Str::random(32));
            } while (QrCode::where('code', $qrCode)->exists());

            //5/ stoer qr

            QrCode::create([
                'student_id' => $student->id,
                'code' => $qrCode,
                'is_active' => true
            ]);


            return $student;
        });



        return response()->json([
            'message' => 'Student and guardian created successfully',
            'student' => $student->load(['guardian', 'qrCode']),

           
        ]);
    }


    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $validatedData = $request->validate([
            'lrn' => ['required', 'digits:12', 'unique:students,lrn,' . $id],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'middle_name' => ['nullable', 'string'],
            'sex' => ['required', 'in:female,male'],
            'address' => ['required', 'string'],
            'birthdate' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],

            // guardian input
            'name' => ['required', 'string'],
            'relationship' => ['required', 'in:mother,father,sibling,guardian'],
            'email' => ['required', 'email']
        ]);

        DB::transaction(function () use ($student, $validatedData) {

            // update student
            $student->update([
                'lrn' => $validatedData['lrn'],
                'first_name' => strip_tags($validatedData['first_name']),
                'last_name' => strip_tags($validatedData['last_name']),
                'middle_name' => isset($validatedData['middle_name'])
                    ? strip_tags($validatedData['middle_name'])
                    : null,
                'sex' => $validatedData['sex'],
                'address' => strip_tags($validatedData['address']),
                'birthdate' => $validatedData['birthdate'],
                'status' => $validatedData['status'],
            ]);

            // update guardian (1:1)
            $student->guardian()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'name' => strip_tags($validatedData['name']),
                    'relationship' => $validatedData['relationship'],
                    'email' => strip_tags($validatedData['email'])
                ]
            );
        });
        

        return response()->json([
            'message' => 'Student information updated successfully',
            'student' => $student->load('guardian')
        ]);
    }

    public function destroy(string $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        try {
            $student->delete();

            return response()->json([
                'message' => 'Student deleted successfully',
                'data' => $student
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $activeSchoolYearId = SchoolYear::query()->where('is_active', true)->value('id');

        $students = Student::where('student_number', 'like', "%{$q}%")
            ->orWhere('first_name', 'like', "%{$q}%")
            ->orWhere('last_name', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'student_number', 'first_name', 'last_name'])
            ->map(function ($student) use ($activeSchoolYearId) {
                $student->is_enrolled = $activeSchoolYearId
                    ? $student->enrollments()->where('school_year_id', $activeSchoolYearId)->exists()
                    : false;

                return $student;
            });

        return response()->json($students);
    }

    public function show($id){
        return Student::with('guardian')->findOrFail($id);
    }
}
